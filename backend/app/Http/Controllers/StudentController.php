<?php

namespace App\Http\Controllers;

use App\Mail\{AssessmentCompletedMail, RecommendationReadyMail};
use App\Models\{ActivityLog, AssessmentAnalysis, AssessmentAttempt, AssessmentQuestion, Career, CounsellingSession, Recommendation, StudentGoal, SubjectCombination, UniversityProgram, User};
use App\Services\CareerPathTrackingService;
use App\Services\IntelligentAnalysisService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\{Auth, DB, Log, Mail};
use App\Services\{AcademicProgressService, RecommendationEngine};
use Illuminate\Http\{JsonResponse, RedirectResponse, Request};
use Illuminate\View\View;

class StudentController extends Controller
{
    public function __construct(
        protected RecommendationEngine $recommendationEngine,
        protected IntelligentAnalysisService $intelligence,
        protected CareerPathTrackingService $pathTracking,
        protected AcademicProgressService $academicProgress,
    ) {}


    /** Scope recommendations to the student's latest completed assessment */
    private function latestAttemptId(int $studentId): ?int
    {
        return AssessmentAttempt::where('student_id', $studentId)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->value('id');
    }

    /** Build a recommendation query scoped to latest attempt when available */
    private function recommendationsFor(int $studentId)
    {
        $query = Recommendation::where('student_id', $studentId);
        $attemptId = $this->latestAttemptId($studentId);

        if ($attemptId) {
            $query->where('attempt_id', $attemptId);
        }

        return $query;
    }

    /** Dashboard */
    public function dashboard(): View
    {
        $user    = Auth::user();
        $profile = $user->studentProfile;

        $latestAttempt = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();
        $assessmentsCompleted = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $topCareers = $this->recommendationsFor($user->id)
            ->where('type', 'career')
            ->orderByDesc('confidence_score')
            ->with(['recommended.universityPrograms'])
            ->take(3)
            ->get();

        $topSubjects = $this->recommendationsFor($user->id)
            ->where('type', 'subject_combination')
            ->orderByDesc('confidence_score')
            ->with('recommended')
            ->take(4)
            ->get();

        $topUniversities = $this->recommendationsFor($user->id)
            ->where('type', 'university_program')
            ->orderByDesc('confidence_score')
            ->with('recommended')
            ->take(3)
            ->get();

        $careerMatches      = $this->recommendationsFor($user->id)->where('type', 'career')->count();
        $subjectMatches     = $this->recommendationsFor($user->id)->where('type', 'subject_combination')->count();
        $universityMatches  = $this->recommendationsFor($user->id)->where('type', 'university_program')->count();

        $recentActivities = ActivityLog::where('user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        $latestAttempt = $latestAttempt?->fresh(['analysis']);
        $analysis = $latestAttempt?->analysis;
        $careerGoal = StudentGoal::where('student_id', $user->id)
            ->with(['career', 'universityProgram'])
            ->first();
        $trackingStatus = $careerGoal ? $this->pathTracking->getTrackingStatus($careerGoal) : null;

        return view('dashboard.student.index', compact(
            'user', 'profile', 'assessmentsCompleted',
            'topCareers', 'topSubjects', 'topUniversities',
            'careerMatches', 'subjectMatches', 'universityMatches',
            'recentActivities', 'analysis', 'careerGoal', 'trackingStatus'
        ));
    }

    /** Show assessment form */
    public function assessment(): View
    {
        $user = Auth::user();

        $existingAttempt = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'in_progress')
            ->first();

        $questions = AssessmentQuestion::where('is_active', true)
            ->with(['options' => fn($q) => $q->orderBy('order')])
            ->orderBy('order')
            ->get();

        return view('dashboard.student.assessment', compact('user', 'existingAttempt', 'questions'));
    }

    /** Submit assessment */
    public function submitAssessment(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'answers'      => ['nullable', 'array'],
            'text_answers' => ['nullable', 'array'],
            'text_answers.*' => ['nullable', 'string', 'max:2000'],
        ]);

        $answers     = $request->input('answers', []);
        $textAnswers = $request->input('text_answers', []);

        $questions = AssessmentQuestion::where('is_active', true)
            ->with(['options'])
            ->get()
            ->keyBy('id');

        try {
            DB::beginTransaction();

            // Start a fresh attempt each time the student submits
            AssessmentAttempt::where('student_id', $user->id)
                ->where('status', 'in_progress')
                ->delete();

            $attempt = AssessmentAttempt::create([
                'student_id' => $user->id,
                'status'     => 'in_progress',
            ]);

            foreach ($questions as $questionId => $question) {
                match ($question->type) {
                    'multiple_choice' => $this->saveMultipleChoiceResponse(
                        $attempt, $question, $answers[$questionId] ?? null
                    ),
                    'scale' => $this->saveScaleResponse(
                        $attempt, $questionId, $answers[$questionId] ?? null
                    ),
                    'checkbox' => $this->saveCheckboxResponses(
                        $attempt, $question, $textAnswers[$questionId] ?? null
                    ),
                    'text' => $this->saveTextResponse(
                        $attempt, $questionId, $textAnswers[$questionId] ?? null
                    ),
                    default => null,
                };
            }

            $requiredMcCount = $questions->whereIn('type', ['multiple_choice', 'scale'])->count();
            $answeredRequired = $attempt->responses()
                ->whereIn('question_id', $questions->whereIn('type', ['multiple_choice', 'scale'])->pluck('id'))
                ->where(function ($q) {
                    $q->whereNotNull('option_id')->orWhereNotNull('scale_value');
                })
                ->count();

            if ($answeredRequired < $requiredMcCount) {
                DB::rollBack();
                return back()->with('error', 'Please answer all questions before submitting. Go back and check any questions you skipped.');
            }

            $attempt->complete();

            ActivityLog::record('assessment_completed', $attempt);

            DB::commit();

            try {
                $this->recommendationEngine->generate($attempt->fresh());
            } catch (\Throwable $engineEx) {
                Log::error('Recommendation generation failed after assessment', [
                    'message' => $engineEx->getMessage(),
                    'attempt' => $attempt->id,
                ]);
            }

            try {
                $careerCount = Recommendation::where('student_id', $user->id)->where('type', 'career')->count();
                $uniCount    = Recommendation::where('student_id', $user->id)->where('type', 'university_program')->count();
                Mail::send(new AssessmentCompletedMail($user, $attempt));
                Mail::send(new RecommendationReadyMail($user, $careerCount, $uniCount));
            } catch (\Throwable $mailEx) {
                Log::warning('Assessment mail failed', ['error' => $mailEx->getMessage()]);
            }

            return redirect()->route('student.recommendations')
                ->with('success', 'Assessment completed! Your personalized recommendations are ready.');

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Assessment submission failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);
            return back()->with('error', 'Failed to submit assessment. Please try again.');
        }
    }

    private function saveMultipleChoiceResponse(
        AssessmentAttempt $attempt,
        AssessmentQuestion $question,
        mixed $optionId
    ): void {
        if (!$optionId) {
            return;
        }

        if (is_array($optionId)) {
            $optionId = (int) ($optionId[0] ?? 0);
        }

        $optionId = (int) $optionId;

        if (!$question->options->contains('id', $optionId)) {
            throw new \InvalidArgumentException("Invalid option for question {$question->id}.");
        }

        $attempt->responses()->updateOrCreate(
            ['question_id' => $question->id],
            ['option_id' => $optionId, 'text_response' => null, 'scale_value' => null]
        );
    }

    private function saveScaleResponse(
        AssessmentAttempt $attempt,
        int $questionId,
        mixed $value
    ): void {
        if ($value === null || $value === '') {
            return;
        }

        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        $scaleValue = (int) $value;

        if ($scaleValue < 1 || $scaleValue > 5) {
            throw new \InvalidArgumentException("Invalid scale value for question {$questionId}.");
        }

        $attempt->responses()->updateOrCreate(
            ['question_id' => $questionId],
            ['scale_value' => $scaleValue, 'option_id' => null, 'text_response' => null]
        );
    }

    private function saveCheckboxResponses(
        AssessmentAttempt $attempt,
        AssessmentQuestion $question,
        ?string $selection
    ): void {
        if (!$selection) {
            return;
        }

        $selectedTexts = array_filter(array_map('trim', explode(' | ', $selection)));

        $attempt->responses()->where('question_id', $question->id)->delete();

        foreach ($question->options as $option) {
            if (in_array($option->option_text, $selectedTexts, true)) {
                $attempt->responses()->create([
                    'question_id' => $question->id,
                    'option_id'   => $option->id,
                ]);
            }
        }
    }

    private function saveTextResponse(
        AssessmentAttempt $attempt,
        int $questionId,
        ?string $text
    ): void {
        if (!$text) {
            return;
        }

        $attempt->responses()->updateOrCreate(
            ['question_id' => $questionId],
            ['text_response' => $text, 'option_id' => null, 'scale_value' => null]
        );
    }

    /** Show recommendations */
    public function recommendations(Request $request): View
    {
        $user = Auth::user();
        $latestAttempt = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();
        $this->refreshAnalysisIfNeeded($user, $latestAttempt);
        $this->ensureRecommendationsGenerated($user, $latestAttempt);
        $type = $request->input('type', 'all');

        $careers = $this->recommendationsFor($user->id)
            ->where('type', 'career')
            ->with(['recommended' => fn ($q) => $q->with(['requiredSubjects', 'universityPrograms.requiredSubjects'])])
            ->orderByDesc('confidence_score')
            ->take(6)
            ->get();

        $totalCareerCount = $this->recommendationsFor($user->id)
            ->where('type', 'career')
            ->count();

        $subjectCombos = $this->recommendationsFor($user->id)
            ->where('type', 'subject_combination')
            ->orderByDesc('confidence_score')
            ->with('recommended.subjects')
            ->take(4)
            ->get();

        $careerGoal = StudentGoal::where('student_id', $user->id)
            ->with(['career', 'universityProgram'])
            ->first();

        return view('dashboard.student.recommendations', compact(
            'user', 'careers', 'subjectCombos', 'type', 'careerGoal', 'totalCareerCount'
        ));
    }

    /** Show academic progress */
    public function progress(Request $request): View
    {
        $user = Auth::user();
        $profile = $user->studentProfile;

        $latestAttemptForRefresh = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();
        $this->refreshAnalysisIfNeeded($user, $latestAttemptForRefresh);
        $this->ensureRecommendationsGenerated($user, $latestAttemptForRefresh);

        $formFilter = $request->input('form');
        $termFilter = $request->input('term');

        $query = $user->academicResults()->with('subject')->orderBy('term')->orderBy('form_level');
        if ($formFilter) {
            $query->where('form_level', $formFilter);
        }
        if ($termFilter) {
            $query->where('term', $termFilter);
        }

        $academicResults = $query->get();
        $allResults = $user->academicResults()->with('subject')->get();

        $availableForms = collect(['Form 1', 'Form 2', 'Form 3', 'Form 4']);
        $availableTerms = $allResults->pluck('term')->unique()->sort()->values();

        $resultsByTerm = $academicResults->groupBy('term');

        $recommendations = Recommendation::where('student_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $latestAttempt = $latestAttemptForRefresh?->fresh(['analysis']);
        $analysis = $latestAttempt?->analysis;

        $careerGoal = StudentGoal::where('student_id', $user->id)
            ->with(['career', 'universityProgram'])
            ->first();

        $trackingStatus = $careerGoal ? $this->pathTracking->getTrackingStatus($careerGoal) : null;
        $gradeTrend = $careerGoal
            ? $this->pathTracking->getGradeTrend($user, $careerGoal->career, $careerGoal->universityProgram)
            : [];

        $academicReport = $this->academicProgress->getFullReport($user, $analysis);

        return view('dashboard.student.progress', compact(
            'user', 'profile', 'academicResults', 'resultsByTerm', 'recommendations',
            'analysis', 'careerGoal', 'trackingStatus', 'gradeTrend',
            'formFilter', 'termFilter', 'availableForms', 'availableTerms', 'allResults',
            'academicReport'
        ));
    }

    /** Subject combination intelligence hub */
    public function subjectCombinations(Request $request): View
    {
        $user = Auth::user();
        $profile = $user->studentProfile;

        $latestAttemptForRefresh = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();
        $this->refreshAnalysisIfNeeded($user, $latestAttemptForRefresh);
        $this->ensureRecommendationsGenerated($user, $latestAttemptForRefresh);

        $targetForm = $request->input('target_form', in_array($profile?->form_level, ['Form 3', 'Form 4']) ? $profile->form_level : 'Form 3');
        if (!in_array($targetForm, ['Form 3', 'Form 4'], true)) {
            $targetForm = 'Form 3';
        }

        $latestAttempt = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with('analysis')
            ->latest('completed_at')
            ->first();

        $analysis = $latestAttempt?->fresh('analysis')?->analysis;
        $recommendationHistory = $this->intelligence->getRecommendationHistory($user);
        $savedPrefs = $profile?->subject_preferences ?? [];
        $analyzed = (bool) session('subject_combinations_analyzed', false);
        session()->forget('subject_combinations_analyzed');
        $mscePaths = $this->intelligence->analyzeMscePaths($user, $analysis, $savedPrefs);

        $combinations = Recommendation::where('student_id', $user->id)
            ->where('type', 'subject_combination')
            ->orderByDesc('confidence_score')
            ->with('recommended.subjects')
            ->take(6)
            ->get();

        $form12Subjects = $user->academicResults()
            ->whereIn('form_level', ['Form 1', 'Form 2', 'Form 3', 'Form 4'])
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(fn ($rows) => [
                'name'  => $rows->first()->subject?->name,
                'score' => round($rows->avg('score'), 1),
            ])
            ->values();
$simulationSubjects = $user->academicResults()
            ->with('subject')
            ->get()
            ->pluck('subject.name')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('dashboard.student.subject-combinations', compact(
            'user', 'profile', 'analysis', 'recommendationHistory', 'combinations',
            'form12Subjects', 'mscePaths', 'targetForm',
            'simulationSubjects', 'savedPrefs', 'analyzed'
        ));
    }

    /** Save (and optionally analyse) the student's subject-combination preference form */
    public function saveSubjectPreferences(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $profile = $user->studentProfile;

        $validated = $request->validate([
            'preferred_subjects'   => ['nullable', 'array'],
            'preferred_subjects.*' => ['string'],
            'career_interests'     => ['nullable', 'string', 'max:2000'],
            'personal_interests'   => ['nullable', 'string', 'max:2000'],
            'career_goals'         => ['nullable', 'string', 'max:2000'],
            'strengths'            => ['nullable', 'array'],
            'strengths.*'          => ['string'],
            'learning_preference'  => ['nullable', 'string', 'max:1000'],
            'additional_info'      => ['nullable', 'string', 'max:1000'],
        ]);

        if ($profile) {
            $profile->update([
                'preferred_career'     => $validated['career_goals'] ?? $profile->preferred_career,
                'subject_preferences'  => [
                    'preferred_subjects'  => $validated['preferred_subjects'] ?? [],
                    'career_interests'    => $validated['career_interests'] ?? '',
                    'personal_interests'  => $validated['personal_interests'] ?? '',
                    'strengths'           => $validated['strengths'] ?? [],
                    'learning_preference' => $validated['learning_preference'] ?? '',
                    'additional_info'     => $validated['additional_info'] ?? '',
                ],
            ]);
        }

        if ($request->input('action') === 'analyze') {
            session()->flash('subject_combinations_analyzed', true);
        }

        return redirect()->route('student.subject.combinations')
            ->with('success', 'Your subject combination profile has been saved.');
    }

    /** Career path goal setting and tracking */
    public function careerPath(): View
    {
        $user = Auth::user();

        $careerGoal = StudentGoal::where('student_id', $user->id)
            ->with(['career.universityPrograms', 'universityProgram.requiredSubjects', 'universityProgram.careers'])
            ->first();

        if ($careerGoal) {
            $this->pathTracking->analyzeGoal($careerGoal);
            $careerGoal->refresh();
        }

        $careers = Career::select('id', 'title', 'category', 'description')
            ->orderBy('title')
            ->get();
        $programs = UniversityProgram::select('id', 'name', 'university', 'faculty', 'minimum_points', 'entry_requirements')
            ->with(['careers:id', 'requiredSubjects:id,name'])
            ->orderBy('university')
            ->orderBy('name')
            ->get();

        $savedCareerRec = Recommendation::where('student_id', $user->id)
            ->where('type', 'career')
            ->where('status', 'accepted')
            ->with('recommended')
            ->first();

        $trackingStatus = $careerGoal ? $this->pathTracking->getTrackingStatus($careerGoal) : null;
        $eligibility = ($careerGoal?->career)
            ? $this->pathTracking->evaluateCareerEligibility($user, $careerGoal->career)
            : null;
        $alternativeCareers = $this->pathTracking->suggestAlternativeCareers(
            $user,
            $careerGoal?->career,
            3
        );
        $gradeTrend = $careerGoal
            ? $this->pathTracking->getGradeTrend($user, $careerGoal->career, $careerGoal->universityProgram)
            : [];

        $universities = $programs->groupBy('university');

        $programsForJs = $programs->map(fn ($p) => [
            'id'         => $p->id,
            'name'       => $p->name,
            'university' => $p->university,
            'faculty'    => $p->faculty,
            'career_ids' => $p->careers->pluck('id')->values(),
        ])->values();

        return view('dashboard.student.career-path', compact(
            'user', 'careerGoal', 'careers', 'programs', 'universities',
            'savedCareerRec', 'trackingStatus', 'gradeTrend', 'programsForJs',
            'eligibility', 'alternativeCareers'
        ));
    }

    /** Save career path goal */
    public function setCareerGoal(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'career_id'             => ['nullable', 'exists:careers,id'],
            'university_program_id' => ['nullable', 'exists:university_programs,id'],
        ]);

        if (empty($data['career_id']) && empty($data['university_program_id'])) {
            return back()->with('error', 'Please select at least a career or a university programme.');
        }

        $this->pathTracking->setGoal(
            $user,
            $data['career_id'] ?? null,
            $data['university_program_id'] ?? null
        );

        ActivityLog::record('career_goal_set', null, $data);

        return redirect()->route('student.career.path')
            ->with('success', 'Your career path goal has been saved. The system will track your progress as your grades are updated.');
    }


    /** What-if simulation: recalculate MSCE paths with a hypothetical subject score */
    public function simulateGradeChange(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject'   => ['required', 'string', 'max:100'],
            'new_score' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        $user = Auth::user();
        $analysis = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first()?->analysis;

        try {
            $result = $this->academicProgress->simulateGradeImprovement(
                $user,
                $analysis,
                $validated['subject'],
                (float) $validated['new_score']
            );

            return response()->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            Log::error('Simulation failed', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => 'Simulation could not be completed. Please try again.'], 422);
        }
    }

    /** Generate recommendations if assessment completed but results are missing */
    private function ensureRecommendationsGenerated(User $user, ?AssessmentAttempt $attempt): void
    {
        if (!$attempt) {
            return;
        }

        $hasCareerRecs = Recommendation::where('student_id', $user->id)
            ->where('attempt_id', $attempt->id)
            ->where('type', 'career')
            ->exists();

        if ($hasCareerRecs) {
            return;
        }

        try {
            $this->recommendationEngine->generate($attempt->fresh());
        } catch (\Throwable $e) {
            Log::error('Recommendation backfill failed', [
                'student_id' => $user->id,
                'attempt_id' => $attempt->id,
                'message'    => $e->getMessage(),
            ]);
        }
    }

    /** Re-run analysis when grades are newer than the last analysis */
    private function refreshAnalysisIfNeeded(User $user, ?AssessmentAttempt $attempt): void
    {
        if (!$attempt) {
            return;
        }

        $latestGradeAt = $user->academicResults()->max('updated_at');
        $analysisAt    = $attempt->analysis?->updated_at;

        if ($latestGradeAt && (!$analysisAt || $latestGradeAt > $analysisAt)) {
            $this->intelligence->analyzeAndStore($attempt, $user);
            $this->recommendationEngine->generate($attempt->fresh());
        }
    }

    /** Save a recommendation */
    public function saveRecommendation(Recommendation $recommendation): RedirectResponse
    {
        abort_if($recommendation->student_id !== Auth::id(), 403);

        $recommendation->accept();
        ActivityLog::record('recommendation_saved', $recommendation);

        return back()->with('success', 'Recommendation saved to your profile.');
    }

    /** Dismiss a recommendation */
    public function dismissRecommendation(Recommendation $recommendation): RedirectResponse
    {
        abort_if($recommendation->student_id !== Auth::id(), 403);

        $recommendation->dismiss();
        ActivityLog::record('recommendation_dismissed', $recommendation);

        return back()->with('info', 'Recommendation dismissed.');
    }

    // Ã¢â€â‚¬Ã¢â€â‚¬ Self-service PDF Reports Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬Ã¢â€â‚¬

    public function downloadCareerMatchReport(): mixed
    {
        $user    = Auth::user();
        $careers = Recommendation::where('student_id', $user->id)
            ->where('type', 'career')
            ->orderByDesc('confidence_score')
            ->with('recommended.subjects')
            ->get();

        ActivityLog::record('report_downloaded', null, ['type' => 'career-match']);

        return Pdf::loadView('pdf.student.career-match', compact('user', 'careers'))
            ->setPaper('a4', 'portrait')
            ->download('career-match-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadSubjectCombinationsReport(): mixed
    {
        $user     = Auth::user();
        $subjects = Recommendation::where('student_id', $user->id)
            ->where('type', 'subject_combination')
            ->orderByDesc('confidence_score')
            ->with('recommended.subjects')
            ->get();

        ActivityLog::record('report_downloaded', null, ['type' => 'subject-combinations']);

        return Pdf::loadView('pdf.student.subject-combinations', compact('user', 'subjects'))
            ->setPaper('a4', 'portrait')
            ->download('subject-combinations-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadUniversityEligibilityReport(): mixed
    {
        $user         = Auth::user();
        $universities = Recommendation::where('student_id', $user->id)
            ->where('type', 'university_program')
            ->orderByDesc('confidence_score')
            ->with('recommended')
            ->get();

        ActivityLog::record('report_downloaded', null, ['type' => 'university-eligibility']);

        return Pdf::loadView('pdf.student.university-eligibility', compact('user', 'universities'))
            ->setPaper('a4', 'portrait')
            ->download('university-eligibility-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadAcademicProgressReport(): mixed
    {
        $user    = Auth::user();
        $profile = $user->studentProfile;

        $academicResults = $user->academicResults()
            ->with('subject')
            ->orderBy('term')
            ->get();

        $resultsByTerm = $academicResults->groupBy('term');

        ActivityLog::record('report_downloaded', null, ['type' => 'academic-progress']);

        return Pdf::loadView('pdf.student.academic-progress', compact('user', 'profile', 'academicResults', 'resultsByTerm'))
            ->setPaper('a4', 'portrait')
            ->download('academic-progress-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadDevelopmentPlanReport(): mixed
    {
        $user    = Auth::user();
        $profile = $user->studentProfile;

        $topCareers = Recommendation::where('student_id', $user->id)
            ->where('type', 'career')
            ->orderByDesc('confidence_score')
            ->with('recommended.requiredSubjects')
            ->take(3)
            ->get();

        $academicResults = $user->academicResults()
            ->with('subject')
            ->orderBy('term')
            ->get();

        ActivityLog::record('report_downloaded', null, ['type' => 'development-plan']);

        return Pdf::loadView('pdf.student.development-plan', compact('user', 'profile', 'topCareers', 'academicResults'))
            ->setPaper('a4', 'portrait')
            ->download('personal-development-plan-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadAssessmentSummaryReport(): mixed
    {
        $user    = Auth::user();
        $attempt = AssessmentAttempt::where('student_id', $user->id)
            ->where('status', 'completed')
            ->with(['responses.question', 'responses.option'])
            ->latest()
            ->first();

        if (!$attempt) {
            return back()->with('info', 'No completed assessment found. Please complete an assessment first.');
        }

        $categoryTally = $attempt->tallyCareerCategories();

        ActivityLog::record('report_downloaded', null, ['type' => 'assessment-summary']);

        return Pdf::loadView('pdf.student.assessment-summary', compact('user', 'attempt', 'categoryTally'))
            ->setPaper('a4', 'portrait')
            ->download('assessment-summary-' . now()->format('Y-m-d') . '.pdf');
    }

    public function downloadSavedRecommendationsReport(): mixed
    {
        $user  = Auth::user();
        $saved = Recommendation::where('student_id', $user->id)
            ->where('status', 'accepted')
            ->orderByDesc('confidence_score')
            ->with('recommended')
            ->get();

        ActivityLog::record('report_downloaded', null, ['type' => 'saved-recommendations']);

        return Pdf::loadView('pdf.student.saved-recommendations', compact('user', 'saved'))
            ->setPaper('a4', 'portrait')
            ->download('saved-recommendations-' . now()->format('Y-m-d') . '.pdf');
    }

    /** Browse all university programs with eligibility check */
    public function universityLibrary(): View
    {
        $user = Auth::user();

        $studentSubjectIds = $user->academicResults()->pluck('subject_id')->unique();
        $hasAcademicData   = $studentSubjectIds->isNotEmpty();

        $programs = UniversityProgram::with(['subjects', 'requiredSubjects', 'careers'])
            ->orderBy('university')
            ->orderBy('name')
            ->get();

        foreach ($programs as $program) {
            $eligibility = $hasAcademicData
                ? $program->calculateEligibility($user)
                : null;
            $program->is_eligible = $eligibility ? $eligibility['eligible'] : null;
            $program->match_score = $eligibility ? $eligibility['match_score'] : null;
            $program->eligibility_details = $eligibility;
        }

        $universities  = $programs->groupBy('university');
        $eligibleCount = $programs->where('is_eligible', true)->count();
        $totalPrograms = $programs->count();
        $faculties     = $programs->pluck('faculty')->unique()->sort()->values();

        return view('dashboard.student.universities', compact(
            'user', 'universities', 'programs', 'hasAcademicData',
            'eligibleCount', 'totalPrograms', 'faculties'
        ));
    }

    /** Show student's counsellor appointments */
    public function appointments(): View
    {
        $user = Auth::user();

        $appointments = CounsellingSession::with(['counsellor'])
            ->where('student_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $counsellors = User::where('role', 'counsellor')
            ->orderBy('name')
            ->get();

        return view('dashboard.student.appointments', compact('user', 'appointments', 'counsellors'));
    }

    /** Book a new appointment with a counsellor */
    public function bookAppointment(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'counsellor_id'    => ['required', 'exists:users,id'],
            'appointment_type' => ['required', 'in:in_person,online,phone'],
            'preferred_date'   => ['required', 'date', 'after:today'],
            'student_reason'   => ['required', 'string', 'max:1000'],
        ]);

        // Verify chosen user is actually a counsellor
        $counsellor = User::where('id', $data['counsellor_id'])->where('role', 'counsellor')->firstOrFail();

        CounsellingSession::create([
            'counsellor_id'    => $counsellor->id,
            'student_id'       => $user->id,
            'scheduled_at'     => $data['preferred_date'],
            'venue'            => null,
            'status'           => 'pending_confirmation',
            'initiated_by'     => 'student',
            'appointment_type' => $data['appointment_type'],
            'student_reason'   => $data['student_reason'],
        ]);

        ActivityLog::record('appointment_booked', null, [
            'counsellor_id' => $counsellor->id,
            'student_id'    => $user->id,
        ]);

        return redirect()->route('student.appointments')
            ->with('success', 'Your appointment request has been sent to ' . $counsellor->name . '. You will be notified once confirmed.');
    }

    /** Cancel a student-initiated appointment */
    public function cancelAppointment(CounsellingSession $session): RedirectResponse
    {
        $user = Auth::user();

        abort_if($session->student_id !== $user->id, 403);
        abort_if(!in_array($session->status, ['pending_confirmation', 'scheduled']), 422);

        $session->update(['status' => 'cancelled']);

        return back()->with('success', 'Appointment cancelled.');
    }

}


