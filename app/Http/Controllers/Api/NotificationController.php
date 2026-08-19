<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    /**
     * Get user notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([]);
        }

        $laravelNotifications = $user->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => 'laravel',
                    'data' => $n->data,
                    'read_at' => $n->read_at ? $n->read_at->toISOString() : null,
                    'created_at' => $n->created_at ? $n->created_at->toISOString() : now()->toISOString(),
                ];
            });

        $companyId = $user->current_company_id;
        $systemNotifications = \DB::table('system_notifications')
            ->where('company_id', $companyId)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => (string)$s->id,
                    'type' => 'system',
                    'data' => [
                        'title' => 'Low Stock Warning',
                        'message' => $s->message,
                        'type' => $s->type ?? 'low_stock',
                        'product_id' => $s->product_id,
                    ],
                    'read_at' => $s->is_read ? ($s->updated_at ?? $s->created_at) : null,
                    'created_at' => $s->created_at,
                ];
            });

        $merged = $laravelNotifications->concat($systemNotifications)
            ->sortByDesc('created_at')
            ->values();

        return response()->json($merged);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, $id): JsonResponse
    {
        // Try system notifications table first if numeric
        if (is_numeric($id)) {
            $affected = \DB::table('system_notifications')
                ->where('id', $id)
                ->where('company_id', $request->user()?->current_company_id)
                ->update(['is_read' => true, 'updated_at' => now()]);
            if ($affected) {
                return response()->json(['message' => 'Notification marked as read']);
            }
        }

        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json(['message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['message' => 'Notification marked as read']);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $request->user()
            ->unreadNotifications
            ->markAsRead();

        \DB::table('system_notifications')
            ->where('company_id', $request->user()?->current_company_id)
            ->where('is_read', false)
            ->update(['is_read' => true, 'updated_at' => now()]);

        return response()->json(['message' => 'All notifications marked as read']);
    }

    /**
     * Get unread notifications count
     */
    public function unreadCount(Request $request): JsonResponse
    {
        $count = $request->user()
            ->unreadNotifications()
            ->count();

        $count += \DB::table('system_notifications')
            ->where('company_id', $request->user()?->current_company_id)
            ->where('is_read', false)
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Get low stock notifications summary
     */
    public function lowStockSummary(Request $request): JsonResponse
    {
        $companyId = $request->user()?->current_company_id;

        $alerts = \DB::table('system_notifications')
            ->where('company_id', $companyId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'message' => $item->message,
                    'type' => $item->type,
                    'product_id' => $item->product_id,
                    'is_read' => (bool)$item->is_read,
                    'created_at' => $item->created_at,
                ];
            });

        return response()->json([
            'alerts' => $alerts,
            'unread_count' => $alerts->count(),
        ]);
    }
}
