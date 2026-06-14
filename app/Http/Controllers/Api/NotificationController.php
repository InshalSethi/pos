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
        $notifications = $request->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        return response()->json($notifications);
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
