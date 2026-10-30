<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'author', 'publisher', 'isbn', 'call_number', 'category', 'subject',
        'publication_year', 'total_copies', 'available_copies', 'shelf_location',
        'status', 'loan_policy', 'cover_url',
    ];

    protected function casts(): array
    {
        return [
            'publication_year' => 'integer',
            'total_copies' => 'integer',
            'available_copies' => 'integer',
        ];
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class)->orderBy('copy_code');
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function isReference(): bool
    {
        return $this->loan_policy === 'reference';
    }

    /**
     * Create physical copies, each with its own unique barcode and copy code.
     */
    public function addCopies(int $count, string $status = 'available'): void
    {
        $prefix = strtoupper(substr((string) preg_replace('/[^A-Za-z]/', '', $this->title), 0, 2)) ?: 'BK';
        $next = $this->copies()->count() + 1;

        for ($i = 0; $i < $count; $i++, $next++) {
            do {
                $barcode = (string) random_int(10000000, 99999999);
            } while (BookCopy::where('barcode', $barcode)->exists());

            $this->copies()->create([
                'barcode' => $barcode,
                'copy_code' => sprintf('%s-%03d', $prefix, $next),
                'status' => $this->isReference() && $status === 'available' ? 'reference' : $status,
                'location' => $this->shelf_location,
            ]);
        }

        $this->syncCopyCounts();
    }

    /**
     * Keep the summary counters used by lists and exports in step with the
     * individual copy records.
     */
    public function syncCopyCounts(): void
    {
        $this->forceFill([
            'total_copies' => $this->copies()->whereNot('status', 'lost')->count(),
            'available_copies' => $this->copies()->where('status', 'available')->count(),
        ])->save();
    }
}
