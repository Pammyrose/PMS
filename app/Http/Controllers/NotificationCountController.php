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
        $isReviewer = $user->isAdmin() || $user->isRegionalOffice();
        $count = $isReviewer
            ? $counts['pendingReviewNotifications']
            : $counts['unreadSubmissionNotifications'];

        return response()
            ->json([
                'count' => $count,
                'version' => $counts['notificationVersion'],
                'label' => $isReviewer
                    ? 'pending locked-period change requests'
                    : 'unread submission notifications',
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }
}
