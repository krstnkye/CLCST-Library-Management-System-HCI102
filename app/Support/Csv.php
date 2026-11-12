<?php

namespace App\Support;

/**
 * Shared helpers for every CSV download, so all of them open correctly in
 * spreadsheet programs and none of them can carry a formula.
 */
class Csv
{
    /** Byte order mark that lets Excel read the file as UTF-8 (names with ñ stay intact). */
    public const BOM = "\xEF\xBB\xBF";

    /** Stops spreadsheet programs from running a value as a formula (CSV injection). */
    public static function safe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'".$value : $value;
    }

    /** @param  array<int, mixed>  $values */
    public static function row($handle, array $values): void
    {
        fputcsv($handle, array_map([self::class, 'safe'], $values));
    }
}
