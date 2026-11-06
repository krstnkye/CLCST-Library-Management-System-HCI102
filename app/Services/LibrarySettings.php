<?php

namespace App\Services;

use App\Models\LibrarySetting;
use Illuminate\Support\Facades\Schema;

/**
 * Circulation policy values: defaults from config/library.php, overridden by
 * rows a super administrator saved in the library_settings table.
 */
class LibrarySettings
{
    /** Keys a super administrator may change, with their validation rules. */
    public const EDITABLE = [
        'loan_days_student' => 'required|integer|min:1|max:60',
        'loan_days_faculty' => 'required|integer|min:1|max:180',
        'max_loans_student' => 'required|integer|min:1|max:20',
        'max_loans_faculty' => 'required|integer|min:1|max:50',
        'max_renewals' => 'required|integer|min:0|max:5',
        'renewal_days' => 'required|integer|min:1|max:60',
        'max_reservations' => 'required|integer|min:1|max:10',
        'pickup_days' => 'required|integer|min:1|max:14',
        'fine_per_day' => 'required|numeric|min:0|max:500',
        'max_fine_per_item' => 'required|numeric|min:0|max:10000',
    ];

    private ?array $overrides = null;

    public function get(string $key): int|float|string
    {
        $value = $this->overrides()[$key] ?? config("library.{$key}");

        return is_numeric($value) ? $value + 0 : $value;
    }

    public function all(): array
    {
        return collect(array_keys(self::EDITABLE))->mapWithKeys(fn (string $key) => [$key => $this->get($key)])->all();
    }

    public function loanDaysFor(string $role): int
    {
        return (int) $this->get($role === 'faculty' ? 'loan_days_faculty' : 'loan_days_student');
    }

    public function maxLoansFor(string $role): int
    {
        return (int) $this->get($role === 'faculty' ? 'max_loans_faculty' : 'max_loans_student');
    }

    public function save(array $values, int $userId): void
    {
        foreach (array_intersect_key($values, self::EDITABLE) as $key => $value) {
            LibrarySetting::updateOrCreate(['key' => $key], ['value' => (string) $value, 'updated_by' => $userId]);
        }

        $this->overrides = null;
    }

    private function overrides(): array
    {
        if ($this->overrides === null) {
            $this->overrides = Schema::hasTable('library_settings')
                ? LibrarySetting::query()->pluck('value', 'key')->all()
                : [];
        }

        return $this->overrides;
    }
}
