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
            'pendingPenroNotifications' => 0,
            'notificationVersion' => '0',
        ];

        if (! $user || ! Schema::hasTable('accomplishment_submissions')) {
            return $counts;
        }

        if ($user->requiresPenroApproval()
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

        if ($user->isPenro()
            && Schema::hasColumn('accomplishment_submissions', 'penro_office_id')) {
            $penroNotifications = AccomplishmentSubmission::query()
                ->where('penro_office_id', $user->office_id);

            $counts['pendingPenroNotifications'] = (clone $penroNotifications)
                ->where('status', 'pending')
                ->count();
            $counts['notificationVersion'] = $this->versionFor(
                $penroNotifications,
                $counts['pendingPenroNotifications']
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
