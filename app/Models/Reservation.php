<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    public const OPEN_STATUSES = ['pending', 'ready'];

    protected $fillable = ['user_id', 'library_book_id', 'handled_by', 'status', 'ready_until', 'closed_at'];

    protected function casts(): array
    {
        return [
            'ready_until' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(LibraryBook::class, 'library_book_id');
    }

    public function scopeOpen(Builder $query): void
    {
        $query->whereIn('status', self::OPEN_STATUSES);
    }

    /** Position in the waiting list for the same title, starting at 1. */
    public function queuePosition(): int
    {
        return static::query()
            ->where('library_book_id', $this->library_book_id)
            ->open()
            ->where('id', '<=', $this->id)
            ->count();
    }

    /** Close pickup holds whose pickup period has passed. Returns how many were closed. */
    public static function expireUncollectedHolds(): int
    {
        $expired = static::query()
            ->where('status', 'ready')
            ->where('ready_until', '<', now())
            ->update(['status' => 'expired', 'closed_at' => now()]);

        if ($expired > 0) {
            AuditLog::record(null, 'reservation.expired', "Closed {$expired} pickup hold(s) that were not collected in time.");
        }

        return $expired;
    }
}
