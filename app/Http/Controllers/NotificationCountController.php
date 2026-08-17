<?php

namespace App\Http\Controllers;

use App\Services\NotificationCountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationCountController extends Controller
{
    public function __invoke(Request $request, NotificationCountService $notificationCounts): JsonResponse
    {
        $user = $request->user();
        $counts = $notificationCounts->for($user);
        $isPenro = $user->isPenro();
        $count = $isPenro
            ? $counts['pendingPenroNotifications']
            : $counts['unreadSubmissionNotifications'];

        return response()
            ->json([
                'count' => $count,
                'version' => $counts['notificationVersion'],
                'label' => $isPenro
                    ? 'pending accomplishment notifications'
                    : 'unread submission notifications',
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
