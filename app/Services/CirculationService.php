<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BookCopy;
use App\Models\Fine;
use App\Models\LibraryBook;
use App\Models\Loan;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Every circulation rule lives here so the student, faculty and librarian
 * screens all enforce the same policy. Each change runs in a transaction and
 * re-reads the affected rows with a lock, so two desks cannot lend the same
 * copy at once.
 */
class CirculationService
{
    public function __construct(private LibrarySettings $settings)
    {
    }

    public function reserve(User $patron, LibraryBook $book): Reservation
    {
        return DB::transaction(function () use ($patron, $book): Reservation {
            $book = LibraryBook::query()->lockForUpdate()->findOrFail($book->id);
            $this->ensurePatron($patron);

            if ($book->status !== 'active') {
                $this->fail('This title is no longer in circulation.');
            }
            if ($book->isReference()) {
                $this->fail('Reference books are for in-library use only and cannot be reserved.');
            }
            if ($patron->reservations()->open()->where('library_book_id', $book->id)->exists()) {
                $this->fail('You already have an open reservation for this title.');
            }
            if ($patron->loans()->active()->whereHas('copy', fn ($copy) => $copy->where('library_book_id', $book->id))->exists()) {
                $this->fail('You are already borrowing a copy of this title.');
            }
            if ($patron->reservations()->open()->count() >= $this->settings->get('max_reservations')) {
                $this->fail('You have reached the limit of '.$this->settings->get('max_reservations').' open reservations.');
            }

            $reservation = $patron->reservations()->create(['library_book_id' => $book->id, 'status' => 'pending']);
            $this->audit($patron, 'reservation.created', 'Reserved "'.$book->title.'".', ['reservation_id' => $reservation->id]);

            return $reservation;
        });
    }

    public function cancelReservation(User $actor, Reservation $reservation): void
    {
        DB::transaction(function () use ($actor, $reservation): void {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if (! in_array($reservation->status, Reservation::OPEN_STATUSES, true)) {
                $this->fail('This reservation is already closed.');
            }

            $reservation->update(['status' => 'cancelled', 'closed_at' => now(), 'handled_by' => $actor->isStaff() ? $actor->id : null]);
            $this->audit($actor, 'reservation.cancelled', 'Cancelled the reservation for "'.$reservation->book->title.'".', ['reservation_id' => $reservation->id]);
        });
    }

    public function markReady(User $staff, Reservation $reservation): void
    {
        DB::transaction(function () use ($staff, $reservation): void {
            $reservation = Reservation::query()->lockForUpdate()->findOrFail($reservation->id);
            if ($reservation->status !== 'pending') {
                $this->fail('Only pending reservations can be marked ready for pickup.');
            }
            if ($reservation->book->copies()->where('status', 'available')->doesntExist()) {
                $this->fail('No copy of this title is on the shelf yet.');
            }

            $reservation->update([
                'status' => 'ready',
                'handled_by' => $staff->id,
                'ready_until' => now()->addDays((int) $this->settings->get('pickup_days'))->endOfDay(),
            ]);
            $this->audit($staff, 'reservation.ready', 'Marked "'.$reservation->book->title.'" ready for '.$reservation->user->name.'.', ['reservation_id' => $reservation->id]);
        });
    }

    public function checkout(User $staff, User $patron, BookCopy $copy): Loan
    {
        return DB::transaction(function () use ($staff, $patron, $copy): Loan {
            $copy = BookCopy::query()->with('book')->lockForUpdate()->findOrFail($copy->id);
            $this->ensurePatron($patron);

            if (! $patron->isActive()) {
                $this->fail($patron->name.'\'s account is not active.');
            }
            if ($copy->book->isReference() || $copy->status === 'reference') {
                $this->fail('Reference copies cannot leave the library.');
            }
            if ($copy->status !== 'available') {
                $this->fail('Copy '.$copy->barcode.' is not available (status: '.str_replace('_', ' ', $copy->status).').');
            }
            if ($patron->loans()->overdue()->exists()) {
                $this->fail($patron->name.' has an overdue book. It must be returned before borrowing again.');
            }
            $limit = $this->settings->maxLoansFor($patron->role);
            if ($patron->loans()->active()->count() >= $limit) {
                $this->fail($patron->name.' already has the maximum of '.$limit.' books on loan.');
            }
            $waitingAhead = Reservation::query()->open()
                ->where('library_book_id', $copy->library_book_id)
                ->where('user_id', '!=', $patron->id)
                ->count();
            $onShelf = $copy->book->copies()->where('status', 'available')->count();
            $ownReservation = $patron->reservations()->open()->where('library_book_id', $copy->library_book_id)->first();
            if (! $ownReservation && $waitingAhead >= $onShelf) {
                $this->fail('Every shelf copy of this title is set aside for patrons on the waiting list.');
            }

            $now = now();
            $loan = Loan::create([
                'user_id' => $patron->id,
                'book_copy_id' => $copy->id,
                'issued_by' => $staff->id,
                'receipt_no' => 'LN-'.$now->format('Ymd').'-'.strtoupper(bin2hex(random_bytes(3))),
                'borrowed_at' => $now,
                'due_at' => $now->copy()->addDays($this->settings->loanDaysFor($patron->role))->endOfDay(),
            ]);

            $copy->update(['status' => 'on_loan']);
            $copy->book->syncCopyCounts();
            $ownReservation?->update(['status' => 'fulfilled', 'closed_at' => $now, 'handled_by' => $staff->id]);
            $this->audit($staff, 'loan.issued', 'Lent "'.$copy->book->title.'" ('.$copy->barcode.') to '.$patron->name.'.', ['loan_id' => $loan->id]);

            return $loan;
        });
    }

    public function renew(User $patron, Loan $loan): Loan
    {
        return DB::transaction(function () use ($patron, $loan): Loan {
            $loan = Loan::query()->with('copy.book')->lockForUpdate()->findOrFail($loan->id);
            if ($loan->user_id !== $patron->id) {
                abort(403, 'You can only renew your own loans.');
            }
            if (! $loan->isActive()) {
                $this->fail('This book has already been returned.');
            }
            if ($loan->isOverdue()) {
                $this->fail('Overdue books cannot be renewed online. Please return it to the library desk.');
            }
            if ($loan->renewals >= $this->settings->get('max_renewals')) {
                $this->fail('This loan has already used its '.$this->settings->get('max_renewals').' allowed renewal(s).');
            }
            $waiting = Reservation::query()->open()->where('library_book_id', $loan->copy->library_book_id)->count();
            if ($waiting > 0 && $loan->copy->book->copies()->where('status', 'available')->count() < $waiting) {
                $this->fail('Other patrons are waiting for this title, so it cannot be renewed.');
            }

            $loan->update([
                'due_at' => $loan->due_at->copy()->addDays((int) $this->settings->get('renewal_days'))->endOfDay(),
                'renewals' => $loan->renewals + 1,
            ]);
            $this->audit($patron, 'loan.renewed', 'Renewed "'.$loan->copy->book->title.'" until '.$loan->due_at->format('M d, Y').'.', ['loan_id' => $loan->id]);

            return $loan;
        });
    }

    /** Check a book back in; returns the overdue fine that was charged, if any. */
    public function checkin(User $staff, Loan $loan): ?Fine
    {
        return DB::transaction(function () use ($staff, $loan): ?Fine {
            $loan = Loan::query()->with(['copy.book', 'user'])->lockForUpdate()->findOrFail($loan->id);
            if (! $loan->isActive()) {
                $this->fail('This book has already been checked in.');
            }

            $loan->update(['returned_at' => now(), 'received_by' => $staff->id]);
            $loan->copy->update(['status' => 'available']);
            $loan->copy->book->syncCopyCounts();

            $fine = null;
            if ($loan->daysLate() > 0) {
                $fine = Fine::create([
                    'user_id' => $loan->user_id,
                    'loan_id' => $loan->id,
                    'reason' => 'Overdue return: '.$loan->copy->book->title,
                    'days_late' => $loan->daysLate(),
                    'amount' => $loan->accruedFine(),
                    'status' => 'outstanding',
                ]);
            }

            $this->audit($staff, 'loan.returned', 'Checked in "'.$loan->copy->book->title.'" ('.$loan->copy->barcode.') from '.$loan->user->name.'.', [
                'loan_id' => $loan->id,
                'fine' => $fine?->amount,
            ]);

            return $fine;
        });
    }

    public function settleFine(User $staff, Fine $fine, string $outcome): Fine
    {
        return DB::transaction(function () use ($staff, $fine, $outcome): Fine {
            $fine = Fine::query()->lockForUpdate()->findOrFail($fine->id);
            if ($fine->status !== 'outstanding') {
                $this->fail('This fine has already been settled.');
            }

            $prefix = $outcome === 'paid' ? 'OR' : 'WV';
            $fine->update([
                'status' => $outcome,
                'settled_by' => $staff->id,
                'settled_at' => now(),
                'receipt_no' => $prefix.'-'.now()->format('Ymd').'-'.str_pad((string) $fine->id, 5, '0', STR_PAD_LEFT),
            ]);
            $this->audit($staff, 'fine.'.$outcome, ucfirst($outcome === 'paid' ? 'recorded payment of' : 'waived').' PHP '.number_format((float) $fine->amount, 2).' for '.$fine->user->name.'.', ['fine_id' => $fine->id]);

            return $fine;
        });
    }

    private function ensurePatron(User $patron): void
    {
        if (! $patron->isPatron()) {
            $this->fail('Only student and faculty accounts can borrow or reserve books.');
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['circulation' => $message]);
    }

    private function audit(User $actor, string $event, string $description, array $metadata = []): void
    {
        AuditLog::record($actor, $event, $description, $metadata);
    }
}
