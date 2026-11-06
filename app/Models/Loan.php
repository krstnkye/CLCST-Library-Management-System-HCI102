<?php

namespace App\Models;

use App\Services\LibrarySettings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Loan extends Model
{
    protected $fillable = [
        'user_id', 'book_copy_id', 'issued_by', 'received_by', 'receipt_no',
        'borrowed_at', 'due_at', 'returned_at', 'renewals',
    ];

    protected function casts(): array
    {
        return [
            'borrowed_at' => 'datetime',
            'due_at' => 'datetime',
            'returned_at' => 'datetime',
            'renewals' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function copy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class, 'book_copy_id');
    }

    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function fine(): HasOne
    {
        return $this->hasOne(Fine::class);
    }

    public function scopeActive(Builder $query): void
    {
        $query->whereNull('returned_at');
    }

    public function scopeOverdue(Builder $query): void
    {
        $query->whereNull('returned_at')->where('due_at', '<', now()->startOfDay());
    }

    public function isActive(): bool
    {
        return $this->returned_at === null;
    }

    public function isOverdue(): bool
    {
        return $this->isActive() && $this->daysRemaining() < 0;
    }

    /** Whole days past the due date, counted until the return (or today). */
    public function daysLate(): int
    {
        $end = ($this->returned_at ?? now())->copy()->startOfDay();

        return max(0, (int) $this->due_at->copy()->startOfDay()->diffInDays($end, false));
    }

    /** Whole days left before the due date; negative once overdue. */
    public function daysRemaining(): int
    {
        return (int) now()->startOfDay()->diffInDays($this->due_at->copy()->startOfDay(), false);
    }

    public function accruedFine(): float
    {
        $settings = app(LibrarySettings::class);

        return (float) min($this->daysLate() * $settings->get('fine_per_day'), $settings->get('max_fine_per_item'));
    }

    public function canRenew(): bool
    {
        return $this->isActive()
            && ! $this->isOverdue()
            && $this->renewals < app(LibrarySettings::class)->get('max_renewals');
    }
}
