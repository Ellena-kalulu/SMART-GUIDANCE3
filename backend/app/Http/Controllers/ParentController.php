<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, AssessmentAttempt, AssessmentAnalysis, PortalMessage, Recommendation, StudentProfile, User};
use App\Services\AcademicProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\{Auth, Schema};
use Illuminate\View\View;

class ParentController extends Controller
{
    public function __construct(protected AcademicProgressService $academicProgress) {}

    /** Dashboard */
    public function dashboard(): View
    {
        $user = Auth::user();
        $children = $user->children()
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations.recommended', 'academicResults'])
            ->get();

        foreach ($children as $child) {
            $child->has_assessment = $child->assessmentAttempts->where('status', 'completed')->isNotEmpty();

            $child->top_career = $child->recommendations
                ->where('type', 'career')
                ->sortByDesc('confidence_score')
                ->first();

            $child->top_subject_combo = $child->recommendations
                ->where('type', 'subject_combination')
                ->sortByDesc('confidence_score')
                ->first();

            $child->best_match_score = $child->recommendations
                ->where('type', 'career')
                ->max('confidence_score') ?? 0;

            $child->avg_score = $child->academicResults->avg('score');

            $latestAnalysis = AssessmentAnalysis::where('student_id', $child->id)
                ->latest('updated_at')
                ->first();
            $child->strengths = $latestAnalysis?->strengths ?? [];
            $child->weaknesses = $latestAnalysis?->weaknesses ?? [];

            $child->risk_alerts = $this->buildRiskAlerts($child);
        }

        $unreadMessages = Schema::hasTable('portal_messages')
            ? PortalMessage::where('recipient_id', $user->id)->whereNull('read_at')->count()
            : 0;

        // Recent activity: assessment completions across all children, newest first
        $childIds = $children->pluck('id');
        $recentActivity = AssessmentAttempt::whereIn('student_id', $childIds)
            ->where('status', 'completed')
            ->with('student')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.parent.index', compact('user', 'children', 'recentActivity', 'unreadMessages'));
    }

    /** List all children */
    public function children(): View
    {
        $user = Auth::user();
        $children = $user->children()
            ->with(['studentProfile', 'academicResults.subject', 'assessmentAttempts', 'recommendations.recommended'])
            ->paginate(10);

        return view('dashboard.parent.children', compact('children'));
    }

    /** View child details */
    public function childDetail(User $user): View
    {
        $parent = Auth::user();

        // Verify parent owns this child
        abort_if(!$parent->children()->where('users.id', $user->id)->exists(), 403);

        $child = $user;

        $child->load(['studentProfile', 'academicResults.subject']);

        $assessments = AssessmentAttempt::where('student_id', $child->id)
            ->with('responses')
            ->latest()
            ->get();

        $recommendations = Recommendation::where('student_id', $child->id)
            ->with('recommended')
            ->orderByDesc('confidence_score')
            ->get();

        $careers = $recommendations->where('type', 'career');
        $universities = $recommendations->where('type', 'university_program');

        $latestAnalysis = AssessmentAnalysis::where('student_id', $child->id)->latest('updated_at')->first();
        $academicReport = $this->academicProgress->getFullReport($child, $latestAnalysis);

        return view('dashboard.parent.child-detail', compact(
            'child', 'assessments', 'recommendations', 'careers', 'universities', 'academicReport'
        ));
    }

    /** View progress reports */
    public function progress(): View
    {
        $user = Auth::user();
        $children = $user->children()->with([
            'studentProfile',
            'academicResults.subject',
            'assessmentAttempts',
            'recommendations.recommended',
        ])->get();

        foreach ($children as $child) {
            $analysis = AssessmentAnalysis::where('student_id', $child->id)->latest('updated_at')->first();
            $child->academic_report = $this->academicProgress->getFullReport($child, $analysis);
        }

        return view('dashboard.parent.progress', compact('children'));
    }

    /** Guardian reports index */
    public function reports(): View
    {
        $user = Auth::user();
        $children = $user->children()->with([
            'studentProfile',
            'academicResults.subject',
            'assessmentAttempts',
            'recommendations.recommended',
        ])->get();

        return view('dashboard.parent.reports', compact('children'));
    }

    /** Switch interface language (parent/guardian) */
    public function setLocale(Request $request): RedirectResponse
    {
        $locale = $request->validate(['locale' => ['required', 'in:en,ny,tum']])['locale'];
        session(['locale' => $locale]);

        return back()->with('success', __('app.language_changed'));
    }

    /** Download a child's progress report as PDF */
    public function downloadChildReport(User $user): mixed
    {
        $parent = Auth::user();
        abort_if(!$parent->children()->where('users.id', $user->id)->exists(), 403);

        $child = $user->load([
            'studentProfile',
            'academicResults.subject',
            'assessmentAttempts.responses',
            'recommendations.recommended',
        ]);

        ActivityLog::record('guardian_report_downloaded', $child);

        $pdf = Pdf::loadView('pdf.guardian-child-report', compact('child'))
            ->setPaper('a4', 'portrait');

        $filename = 'progress-report-' . str_replace(' ', '-', strtolower($child->name)) . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /** Parent communication portal */
    public function messages(Request $request): View
    {
        $parent = Auth::user();
        $children = $parent->children()->with('studentProfile')->get();
        $childIds = $children->pluck('id');

        $teachers = User::where('role', 'teacher')->where('is_active', true)->orderBy('name')->get();
        $counsellors = User::where('role', 'counsellor')->where('is_active', true)->orderBy('name')->get();

        $withUser = $request->input('with');
        $composeHint = $request->input('compose');
        $conversation = collect();
        $partner = null;
        $inbox = collect();

        if (!Schema::hasTable('portal_messages')) {
            return view('dashboard.parent.messages', compact(
                'parent', 'children', 'teachers', 'counsellors', 'conversation', 'partner', 'inbox', 'withUser', 'composeHint'
            ))->with('error', __('parent.messages_table_missing'));
        }

        if ($withUser) {
            $partner = User::find($withUser);
            abort_if(!$partner, 404);
            abort_if(!$this->canMessage($parent, $partner, $childIds), 403);

            $conversation = PortalMessage::where(function ($q) use ($parent, $partner) {
                $q->where('sender_id', $parent->id)->where('recipient_id', $partner->id);
            })->orWhere(function ($q) use ($parent, $partner) {
                $q->where('sender_id', $partner->id)->where('recipient_id', $parent->id);
            })->with(['sender', 'student'])->orderBy('created_at')->get();

            PortalMessage::where('recipient_id', $parent->id)
                ->where('sender_id', $partner->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $inbox = PortalMessage::where('recipient_id', $parent->id)
            ->with(['sender', 'student'])
            ->latest()
            ->take(20)
            ->get();

        return view('dashboard.parent.messages', compact(
            'parent', 'children', 'teachers', 'counsellors', 'conversation', 'partner', 'inbox', 'withUser', 'composeHint'
        ));
    }

    public function sendMessage(Request $request): RedirectResponse
    {
        $parent = Auth::user();
        $childIds = $parent->children()->pluck('users.id');

        if (!Schema::hasTable('portal_messages')) {
            return back()->with('error', __('parent.messages_table_missing'));
        }

        $data = $request->validate([
            'recipient_id' => ['required', 'exists:users,id'],
            'student_id'   => ['nullable', 'exists:users,id'],
            'subject'      => ['nullable', 'string', 'max:150'],
            'body'         => ['required', 'string', 'max:3000'],
        ]);

        $recipient = User::findOrFail($data['recipient_id']);
        abort_if(!$this->canMessage($parent, $recipient, $childIds), 403);

        if (!empty($data['student_id']) && !$childIds->contains($data['student_id'])) {
            abort(403);
        }

        PortalMessage::create([
            'sender_id'    => $parent->id,
            'recipient_id' => $recipient->id,
            'student_id'   => $data['student_id'] ?? null,
            'subject'      => $data['subject'] ?: __('parent.message_about_child'),
            'body'         => $data['body'],
        ]);

        ActivityLog::record('parent_message_sent', $recipient, ['student_id' => $data['student_id'] ?? null]);

        return redirect()
            ->route('parent.messages', ['with' => $recipient->id])
            ->with('success', __('parent.message_sent'));
    }

    private function canMessage(User $parent, User $recipient, $childIds): bool
    {
        if ($recipient->isTeacher() || $recipient->isCounsellor()) {
            return true;
        }

        if ($recipient->isStudent() && $childIds->contains($recipient->id)) {
            return true;
        }

        return false;
    }

    private function buildRiskAlerts(User $child): array
    {
        $alerts = [];
        $results = $child->academicResults->sortBy('term');

        if ($results->count() >= 2) {
            $byTerm = $results->groupBy('term');
            $terms = $byTerm->keys()->values();
            if ($terms->count() >= 2) {
                $prev = $byTerm->get($terms[$terms->count() - 2])->avg('score');
                $latest = $byTerm->get($terms->last())->avg('score');
                if ($latest < $prev - 5) {
                    $alerts[] = __('parent.risk_grade_drop', [
                        'name' => $child->name,
                        'drop' => round($prev - $latest, 1),
                    ]);
                }
            }
        }

        $careerRecs = $child->recommendations
            ->where('type', 'career')
            ->sortByDesc('updated_at')
            ->take(2);

        if ($careerRecs->count() === 2) {
            $new = $careerRecs->first();
            $old = $careerRecs->last();
            if ($new->recommended_id !== $old->recommended_id) {
                $alerts[] = __('parent.risk_recommendation_changed', ['name' => $child->name]);
            }
        }

        return $alerts;
    }
}
