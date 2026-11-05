<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Downloads the audit log (optionally filtered by the same search box as the page) as CSV.
 */
class AuditLogExportController extends Controller
{
    public function __invoke(Request $request): StreamedResponse
    {
        $q = trim((string) $request->query('q', ''));
        $from = $request->date('from');
        $to = $request->date('to');

        $query = AuditLog::with('user')
            ->when($q !== '', fn ($query) => $query->where(fn ($where) => $where->where('description', 'like', "%{$q}%")->orWhere('event', 'like', "%{$q}%")))
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from->startOfDay()))
            ->when($to, fn ($query) => $query->where('created_at', '<=', $to->endOfDay()))
            ->latest();

        AuditLog::record($request->user(), 'audit.exported', 'Exported the audit log'.($q !== '' ? ' filtered by "'.$q.'"' : '').'.', ['q' => $q]);

        $filename = 'clcst-audit-log-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query): void {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // lets Excel read the file as UTF-8
            fputcsv($out, ['Date and time', 'User', 'Role', 'School ID', 'Event', 'Details', 'IP address']);
            $query->chunk(500, function ($logs) use ($out): void {
                foreach ($logs as $log) {
                    fputcsv($out, array_map([$this, 'safe'], [
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->user?->name ?? 'Guest / system',
                        $log->user?->roleLabel() ?? '',
                        $log->user?->patron_id ?? '',
                        $log->event,
                        $log->description,
                        $log->ip_address,
                    ]));
                }
            });
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'no-store, private']);
    }

    /** Stops spreadsheet apps from running a value as a formula. */
    private function safe(mixed $value): string
    {
        $value = (string) $value;

        return preg_match('/^[=+\-@\t\r]/', $value) ? "'{$value}" : $value;
    }
}
