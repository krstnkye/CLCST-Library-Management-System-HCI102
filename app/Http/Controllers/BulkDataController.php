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
        return view('management.bulk-data', [
            'userCount' => User::count(),
            'bookCount' => LibraryBook::count(),
            'canManageUsers' => $request->user()->hasAnyRole(['admin', 'superadmin']),
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
        $headers = array_map(fn ($value) => Str::snake(trim((string) $value)), fgetcsv($handle) ?: []);
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

        DB::transaction(function () use ($type, $rows): void {
            foreach ($rows as $index => $row) {
                $type === 'users' ? $this->importUser($row, $index + 2) : $this->importBook($row, $index + 2);
            }
        });

        $this->audit($request, "Imported ".count($rows)." {$type} records.", ['type' => $type, 'count' => count($rows)]);

        return back()->with('status', count($rows)." {$type} records were imported successfully.");
    }

    public function export(Request $request, string $type): StreamedResponse
    {
        abort_unless(in_array($type, ['users', 'books'], true), 404);
        abort_if($type === 'users' && ! $request->user()->hasAnyRole(['admin', 'superadmin']), 403);

        $headers = $type === 'users' ? self::USER_HEADERS : self::BOOK_HEADERS;
        $records = $type === 'users'
            ? User::query()->orderBy('name')->get($headers)
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

    private function importUser(array $row, int $line): void
    {
        if (! in_array($row['role'], User::ROLES, true) || ! in_array($row['status'], ['pending', 'active', 'suspended'], true)) {
            throw ValidationException::withMessages(['csv_file' => "Invalid role or status on line {$line}."]);
        }
        if (! filter_var($row['email'], FILTER_VALIDATE_EMAIL) || $row['patron_id'] === '' || $row['name'] === '') {
            throw ValidationException::withMessages(['csv_file' => "Missing or invalid user information on line {$line}."]);
        }

        $user = User::firstOrNew(['email' => mb_strtolower($row['email'])]);
        $user->fill([
            'patron_id' => strtoupper($row['patron_id']),
            'name' => $row['name'],
            'role' => $row['role'],
            'status' => $row['status'],
            'program_or_department' => $row['program_or_department'] ?: null,
            'mobile_number' => $row['mobile_number'] ?: null,
            'approved_at' => $row['status'] === 'active' ? ($user->approved_at ?? now()) : null,
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
        if ($row['title'] === '' || $row['author'] === '' || $row['isbn'] === '' || $total === false || $available === false || $available < 0 || $total < $available) {
            throw ValidationException::withMessages(['csv_file' => "Invalid book information on line {$line}."]);
        }

        LibraryBook::updateOrCreate(['isbn' => $row['isbn']], [
            'title' => $row['title'],
            'author' => $row['author'],
            'call_number' => $row['call_number'] ?: null,
            'category' => $row['category'] ?: null,
            'publication_year' => $row['publication_year'] !== '' ? (int) $row['publication_year'] : null,
            'total_copies' => $total,
            'available_copies' => $available,
            'shelf_location' => $row['shelf_location'] ?: null,
            'status' => in_array($row['status'], ['active', 'archived'], true) ? $row['status'] : 'active',
        ]);
    }

    private function safeCsvValue(mixed $value): string
    {
        $value = (string) $value;
        return preg_match('/^[=+\-@]/', $value) ? "'{$value}" : $value;
    }

    private function audit(Request $request, string $description, array $metadata): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'event' => 'bulk-data',
            'description' => $description,
            'ip_address' => $request->ip(),
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 1000),
            'metadata' => $metadata,
        ]);
    }
}
