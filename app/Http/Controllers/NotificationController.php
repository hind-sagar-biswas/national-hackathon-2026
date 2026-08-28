<?php

namespace App\Http\Controllers;

use App\Http\Resources\NotificationResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class NotificationController extends Controller
{
    protected function resolvePerPage(Request $request): int
    {
        $default = config('app.feature.pagination');
        $value = (int) $request->integer('per_page', $default);

        return max(1, min($value, $default));
    }

    /**
     * Display notifications page
     */
    public function __invoke(Request $request)
    {
        /** @var User */
        $user = Auth::user();

        $filter = $request->input('filter', 'all'); // all, unread, read

        $query = $user->notifications()->orderBy('created_at', 'desc');

        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        return inertia('Notifications/Index', [
            'list' => Inertia::defer(fn () => NotificationResource::collection(
                $query->paginate($this->resolvePerPage($request))->withQueryString()
            )),
            'filter' => $filter,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Get notifications via API
     */
    public function index(Request $request)
    {
        /** @var User */
        $user = Auth::user();

        $notifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate($this->resolvePerPage($request))
            ->withQueryString();

        return ApiResponse::success([
            'notifications' => $notifications->items(),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ],
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead($id)
    {
        /** @var User */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return ApiResponse::success([
            'unread_count' => $user->unreadNotifications()->count(),
        ], 'Notification marked as read');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        /** @var User */
        $user = Auth::user();

        $user->unreadNotifications->markAsRead();

        return ApiResponse::success([
            'unread_count' => $user->unreadNotifications()->count(),
        ], 'All notifications marked as read');
    }

    /**
     * Delete a notification
     */
    public function destroy($id)
    {

        /** @var User */
        $user = Auth::user();

        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return ApiResponse::success([
            'unread_count' => $user->unreadNotifications()->count(),
        ], 'Notification deleted');
    }
}
