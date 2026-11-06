<?php

namespace App\Http\Controllers;

use App\Support\PatronNotifications;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Read and unread marks for the patron notification feed. Each action answers
 * with JSON for the in-page buttons and with a redirect when JavaScript is off.
 */
class NotificationController extends Controller
{
    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        PatronNotifications::markRead($user, PatronNotifications::for($user)->pluck('key')->all());

        return $this->respond($request, 'All notifications are marked as read.');
    }

    public function unreadAll(Request $request): JsonResponse|RedirectResponse
    {
        PatronNotifications::markUnread($request->user());

        return $this->respond($request, 'All notifications are marked as unread.');
    }

    public function read(Request $request, string $key): JsonResponse|RedirectResponse
    {
        $this->ensureOwnKey($request, $key);
        PatronNotifications::markRead($request->user(), [$key]);

        return $this->respond($request, 'Notification marked as read.');
    }

    public function unread(Request $request, string $key): JsonResponse|RedirectResponse
    {
        $this->ensureOwnKey($request, $key);
        PatronNotifications::markUnread($request->user(), [$key]);

        return $this->respond($request, 'Notification marked as unread.');
    }

    /** Only keys from the patron's own current feed can be stored. */
    private function ensureOwnKey(Request $request, string $key): void
    {
        abort_unless(PatronNotifications::for($request->user())->contains('key', $key), 404);
    }

    private function respond(Request $request, string $message): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'unread' => PatronNotifications::unreadCount($request->user()),
            ]);
        }

        return back()->with('status', $message);
    }
}
