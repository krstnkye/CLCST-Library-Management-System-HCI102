<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\LibraryBook;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BulkDataController extends Controller
{
    private const USER_HEADERS = ['patron_id', 'name', 'email', 'role', 'status', 'program_or_department', 'mobile_number'];

    private const BOOK_HEADERS = ['title', 'author', 'isbn', 'call_number', 'category', 'publication_year', 'total_copies', 'available_copies', 'shelf_location', 'status'];

    public function index(Request $request): View
    {
        $actor = $request->user();

        return view('management.bulk-data', [
            'userCount' => User::query()->whereIn('role', $actor->manageableRoles())->count(),
            'bookCount' => LibraryBook::count(),
            'canManageUsers' => $actor->hasAnyRole(['admin', 'superadmin']),
        ]);
    }

    public function import(Request $request, string $type): RedirectResponse
    {
        abort_unless(in_array($type, ['users', 'books'], true), 404);
        abort_if($type === 'users' && ! $request->user()->hasAnyRole(['admin', 'superadmin']), 403);

        $request->validate(['csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240']]);
        $handle = fopen($request->file('csv_file')->getRealPath(), 'rb');
        if ($handle === false) {
            throw ValidationException::withMessages(['csv_file' => 'The CSV file could not be read.']);
        }

        $expected = $type === 'users' ? self::USER_HEADERS : self::BOOK_HEADERS;
        $headers = array_map(
            fn ($value) => Str::snake(ltrim(trim((string) $value), "\xEF\xBB\xBF")),
            fgetcsv($handle) ?: [],
        );
        if ($headers !== $expected) {
            fclose($handle);
            throw ValidationException::withMessages([
                'csv_file' => 'The CSV headings do not match the required template. Download the template and try again.',
            ]);
        }

        $rows = [];
        while (($values = fgetcsv($handle)) !== false) {
            if (count(array_filter($values, fn ($value) => trim((string) $value) !== '')) === 0) {
                continue;
            }
            if (count($values) !== count($headers)) {
                fclose($handle);
                throw ValidationException::withMessages(['csv_file' => 'One or more rows have the wrong number of columns.']);
            }
            $rows[] = array_combine($headers, array_map(fn ($value) => trim((string) $value), $values));
            if (count($rows) > 2000) {
                fclose($handle);
                throw ValidationException::withMessages(['csv_file' => 'A single import is limited to 2,000 records.']);
            }
        }
        fclose($handle);

        if ($rows === []) {
            throw ValidationException::withMessages(['csv_file' => 'The CSV file does not contain any records to import.']);
        }

        $actor = $request->user();
        DB::transaction(function () use ($request, $type, $rows, $actor): void {
            foreach ($rows as $index => $row) {
                $type === 'users' ? $this->importUser($row, $index + 2, $actor) : $this->importBook($row, $index + 2);
            }
            $this->audit($request, "Imported ".count($rows)." {$type} records.", ['type' => $type, 'count' => count($rows)]);
        });

        return back()->with('status', count($rows)." {$type} records were imported successfully.");
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['users', 'books'], true), 404);
        abort_if($type === 'users' && ! $request->user()->hasAnyRole(['admin', 'superadmin']), 403);

        $headers = $type === 'users' ? self::USER_HEADERS : self::BOOK_HEADERS;
        $records = $type === 'users'
            ? User::query()->whereIn('role', $request->user()->manageableRoles())->orderBy('name')->get($headers)
            : LibraryBook::query()->orderBy('title')->get($headers);

        $this->audit($request, "Exported {$type} records.", ['type' => $type, 'count' => $records->count()]);

        return response()->streamDownload(function () use ($headers, $records): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, $headers);
            foreach ($records as $record) {
                fputcsv($output, collect($headers)->map(fn ($header) => $this->safeCsvValue($record->{$header}))->all());
            }
            fclose($output);
        }, $type.'-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function template(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['users', 'books'], true), 404);
        abort_if($type === 'users' && ! $request->user()->hasAnyRole(['admin', 'superadmin']), 403);
        $headers = $type === 'users' ? self::USER_HEADERS : self::BOOK_HEADERS;

        return response()->streamDownload(function () use ($headers): void {
            $output = fopen('php://output', 'wb');
            fputcsv($output, $headers);
            fclose($output);
        }, $type.'-import-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function importUser(array $row, int $line, User $actor): void
    {
        $email = mb_strtolower(trim($row['email']));
        $patronId = mb_strtoupper(trim($row['patron_id']));

        if (! in_array($row['role'], User::ROLES, true) || ! in_array($row['status'], ['pending', 'active', 'suspended'], true)) {
            throw ValidationException::withMessages(['csv_file' => "Invalid role or status on line {$line}."]);
        }
        if (! filter_var($email, FILTER_VALIDATE_EMAIL) || $patronId === '' || $row['name'] === '' || mb_strlen($email) > 255 || mb_strlen($patronId) > 100 || mb_strlen($row['name']) > 255 || mb_strlen($row['program_or_department']) > 150 || mb_strlen($row['mobile_number']) > 30) {
            throw ValidationException::withMessages(['csv_file' => "Missing or invalid user information on line {$line}."]);
        }
        if (! User::isInstitutionalEmail($email)) {
            throw ValidationException::withMessages(['csv_file' => "The email on line {$line} is not a school email (@".config('library.allowed_email_domain').').']);
        }

        if (! $actor->canManageRole($row['role'])) {
            throw ValidationException::withMessages(['csv_file' => "You cannot import the account role on line {$line}."]);
        }

        $user = User::firstOrNew(['email' => $email]);
        if ($user->exists && (! $actor->canManageRole($user->role) || $actor->is($user))) {
            throw ValidationException::withMessages(['csv_file' => "You cannot change the existing account on line {$line}."]);
        }

        $patronIdInUse = User::query()
            ->where('patron_id', $patronId)
            ->when($user->exists, fn ($query) => $query->where('id', '!=', $user->getKey()))
            ->exists();
        if ($patronIdInUse) {
            throw ValidationException::withMessages(['csv_file' => "The patron ID on line {$line} is already assigned to another account."]);
        }

        $user->forceFill([
            'patron_id' => $patronId,
            'name' => $row['name'],
            'role' => $row['role'],
            'status' => $row['status'],
            'program_or_department' => $row['program_or_department'] ?: null,
            'mobile_number' => $row['mobile_number'] ?: null,
            'approved_at' => $row['status'] === 'active' ? ($user->approved_at ?? now()) : null,
            'approved_by' => $row['status'] === 'active' ? ($user->approved_by ?? $actor->id) : null,
        ]);
        if (! $user->exists) {
            $user->password = Hash::make(Str::password(20));
        }
        $user->save();
    }

    private function importBook(array $row, int $line): void
    {
        $total = filter_var($row['total_copies'], FILTER_VALIDATE_INT);
        $available = filter_var($row['available_copies'], FILTER_VALIDATE_INT);
        $year = $row['publication_year'] === '' ? null : filter_var($row['publication_year'], FILTER_VALIDATE_INT);
        $validLengths = mb_strlen($row['title']) <= 255
            && mb_strlen($row['author']) <= 255
            && mb_strlen($row['isbn']) <= 32
            && mb_strlen($row['call_number']) <= 80
            && mb_strlen($row['category']) <= 100
            && mb_strlen($row['shelf_location']) <= 100;
        $validYear = $year === null || ($year !== false && $year >= 1000 && $year <= (int) date('Y') + 1);
        if ($row['title'] === '' || $row['author'] === '' || $row['isbn'] === '' || ! $validLengths || ! $validYear || $total === false || $available === false || $total < 0 || $available < 0 || $total < $available || $total > 500 || ! in_array($row['status'], ['active', 'archived'], true)) {
            throw ValidationException::withMessages(['csv_file' => "Invalid book information on line {$line}."]);
        }

        $book = LibraryBook::updateOrCreate(['isbn' => $row['isbn']], [
            'title' => $row['title'],
            'author' => $row['author'],
            'call_number' => $row['call_number'] ?: null,
            'category' => $row['category'] ?: null,
            'publication_year' => $year,
            'shelf_location' => $row['shelf_location'] ?: null,
            'status' => $row['status'],
        ]);

        // Copies are tracked one by one, so create any the file adds. Copies
        // beyond the available count start as "maintenance" (not yet shelved).
        $missing = $total - $book->copies()->whereNot('status', 'lost')->count();
        if ($missing > 0) {
            $shelved = max(0, min($missing, $available - $book->copies()->where('status', 'available')->count()));
            $book->addCopies($shelved);
            $book->addCopies($missing - $shelved, 'maintenance');
        }
    }

    private function safeCsvValue(mixed $value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }

    private function audit(Request $request, string $description, array $metadata): void
    {
        AuditLog::record($request->user(), 'bulk-data', $description, $metadata);
    }
}
