<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentSubmission;
use App\Models\FinancialTarget;
use App\Models\PhysicalTarget;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'declined'], true)
            ? (string) $request->query('status')
            : 'pending';

        $baseQuery = AccomplishmentSubmission::query()
            ->where('user_id', $request->user()->id);

        $submissions = $baseQuery
            ->with(['submitter:id,name', 'office:id,name', 'program:id,name', 'indicator:id,name', 'reviewer:id,name'])
            ->where('status', $status)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $submissions->getCollection()->each(function (AccomplishmentSubmission $submission) {
            $targetModel = $submission->submission_type === 'financial'
                ? FinancialTarget::class
                : PhysicalTarget::class;

            $target = $targetModel::query()->where([
                'sector' => $submission->sector,
                'year' => $submission->year,
                'office_id' => $submission->office_id,
                'row_id' => $submission->row_id,
                'indicator_id' => $submission->indicator_id,
            ])->first();

            $submission->setAttribute('target_payload', $target?->toArray() ?? []);
        });

        $unreadIds = $submissions->getCollection()
            ->filter(fn (AccomplishmentSubmission $submission) =>
                in_array($submission->status, ['approved', 'declined'], true)
                && $submission->user_read_at === null
            )
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        if ($unreadIds !== []) {
            AccomplishmentSubmission::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('id', $unreadIds)
                ->update(['user_read_at' => now()]);
        }

        return view('users.submissions.index', compact('submissions', 'status', 'unreadIds'));
    }
}
