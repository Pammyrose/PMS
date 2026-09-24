<?php

namespace App\Http\Controllers;

use App\Models\AccomplishmentSubmission;
use App\Models\FinancialTarget;
use App\Models\PhysicalTarget;
use App\Services\AccomplishmentSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PenroSubmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ['pending', 'approved', 'declined'], true)
            ? (string) $request->query('status')
            : 'pending';

        $submissions = AccomplishmentSubmission::query()
            ->with(['submitter:id,name', 'office:id,name', 'program:id,name', 'indicator:id,name', 'reviewer:id,name'])
            ->where('status', $status)
            ->latest()
            ->paginate(25)
            ->withQueryString();

        $pendingCount = AccomplishmentSubmission::query()
            ->where('status', 'pending')
            ->count();

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

        return view('penro.submissions.index', compact('submissions', 'status', 'pendingCount'));
    }

    public function approve(
        Request $request,
        AccomplishmentSubmission $submission,
        AccomplishmentSubmissionService $submissionService
    ): RedirectResponse {
        $this->authorizeSubmission($request, $submission);
        $submissionService->approve($submission, $request->user());

        return back()->with('success', 'The accomplishment submission was approved and saved.');
    }

    public function decline(Request $request, AccomplishmentSubmission $submission): RedirectResponse
    {
        $this->authorizeSubmission($request, $submission);

        $validated = $request->validate([
            'review_notes' => ['required', 'string', 'max:1000'],
        ]);

        $updated = AccomplishmentSubmission::query()
            ->whereKey($submission->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'declined',
                'reviewed_by' => $request->user()->id,
                'review_notes' => trim($validated['review_notes']),
                'reviewed_at' => now(),
                'user_read_at' => null,
                'updated_at' => now(),
            ]);

        abort_if($updated === 0, 409, 'This submission has already been reviewed.');

        return redirect()
            ->route('accomplishment-requests.index', ['status' => 'declined'])
            ->withFragment('submission-'.$submission->id)
            ->with('success', 'The accomplishment submission was declined. The reason has been saved.');
    }

    private function authorizeSubmission(Request $request, AccomplishmentSubmission $submission): void
    {
        abort_unless(
            $request->user()?->isAdmin() || $request->user()?->isRegionalOffice(),
            403
        );
    }
}
