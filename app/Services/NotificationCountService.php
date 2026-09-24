<?php

namespace App\Services;

use App\Models\AccomplishmentSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

class NotificationCountService
{
    public function for(?User $user): array
    {
        $counts = [
            'unreadSubmissionNotifications' => 0,
            'pendingReviewNotifications' => 0,
            'notificationVersion' => '0',
        ];

        if (! $user || ! Schema::hasTable('accomplishment_submissions')) {
            return $counts;
        }

        if (! ($user->isAdmin() || $user->isRegionalOffice())
            && Schema::hasColumn('accomplishment_submissions', 'user_read_at')) {
            $userNotifications = AccomplishmentSubmission::query()
                ->where('user_id', $user->id);

            $counts['unreadSubmissionNotifications'] = (clone $userNotifications)
                ->whereIn('status', ['approved', 'declined'])
                ->whereNull('user_read_at')
                ->count();
            $counts['notificationVersion'] = $this->versionFor(
                $userNotifications,
                $counts['unreadSubmissionNotifications']
            );
        }

        if ($user->isAdmin() || $user->isRegionalOffice()) {
            $reviewNotifications = AccomplishmentSubmission::query();

            $counts['pendingReviewNotifications'] = (clone $reviewNotifications)
                ->where('status', 'pending')
                ->count();
            $counts['notificationVersion'] = $this->versionFor(
                $reviewNotifications,
                $counts['pendingReviewNotifications']
            );
        }

        return $counts;
    }

    private function versionFor(Builder $query, int $relevantCount): string
    {
        $total = (clone $query)->count();
        $latestUpdate = (string) ((clone $query)->max('updated_at') ?? '');

        return hash('sha256', $total.'|'.$latestUpdate.'|'.$relevantCount);
    }
}
