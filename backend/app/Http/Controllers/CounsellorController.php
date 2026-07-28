<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, AssessmentAttempt, AssessmentAnalysis, Career, CounsellingSession, Recommendation, StudentProfile, User};
use App\Services\AcademicProgressService;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CounsellorController extends Controller
{
    public function __construct(protected AcademicProgressService $academicProgress) {}

    /** Dashboard */
    public function dashboard(): View
    {
        $user = Auth::user();
        $counsellorId = $user->id;
        $studentIds = User::where('role', 'student')->pluck('id');

        $totalStudents = $studentIds->count();

        $assessmentsCompleted = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');

        $careerPathways = Career::count();

        $avgMatchScore = Recommendation::whereIn('student_id', $studentIds)
            ->where('type', 'career')
            ->avg('confidence_score') ?? 0;

        $successRate = $totalStudents > 0
            ? round(($assessmentsCompleted / $totalStudents) * 100)
            : 0;

        $studentsWithoutAssessment = $totalStudents - $assessmentsCompleted;

        // Students who have submitted the career assessment â€” most recent first
        $recentAttempts = AssessmentAttempt::where('status', 'completed')
            ->with(['student.studentProfile'])
            ->orderByRaw('COALESCE(completed_at, updated_at) DESC')
            ->get()
            ->unique('student_id')
            ->take(5)
            ->values();

        $studentsNeedingGuidance = $recentAttempts->map(function ($attempt) {
            $student = $attempt->student;
            if (!$student) {
                return null;
            }
            $student->assessment_completed_at = $attempt->completed_at ?? $attempt->updated_at;
            $best = Recommendation::where('student_id', $student->id)
                ->where('type', 'career')
                ->max('confidence_score') ?? 0;
            $student->guidance_reason = 'Best career match: ' . round($best) . '%';
            return $student;
        })->filter(fn($s) => $s !== null)->values();

        $pendingSessions = CounsellingSession::where('counsellor_id', $counsellorId)
            ->where('status', 'pending_confirmation')
            ->count();

        $upcomingSessions = CounsellingSession::with('student')
            ->where('counsellor_id', $counsellorId)
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->take(5)
            ->get();

        $recentSessions = CounsellingSession::with('student')
            ->where('counsellor_id', $counsellorId)
            ->orderByDesc('updated_at')
            ->take(5)
            ->get();

        // Career interest distribution for chart
        $careerInterests = Recommendation::whereIn('student_id', $studentIds)
            ->where('type', 'career')
            ->with('recommended')
            ->get()
            ->groupBy(fn($r) => $r->recommended?->category ?? 'Other')
            ->map->count()
            ->sortDesc()
            ->take(6)
            ->toArray();

        if (empty($careerInterests)) {
            $careerInterests = ['No data yet' => 0];
        }

        // Assessment completion trend (last 6 months)
        $trend = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE_FORMAT(completed_at, "%b %Y") as month'), DB::raw('count(*) as cnt'))
            ->groupBy('month')
            ->orderByRaw('MIN(completed_at)')
            ->limit(6)
            ->get();

        $chartData = [
            'career_interests' => $careerInterests,
            'trend_labels'     => $trend->pluck('month')->toArray() ?: ['No data'],
            'trend_data'       => $trend->pluck('cnt')->toArray() ?: [0],
        ];

        return view('dashboard.counsellor.index', compact(
            'user', 'totalStudents', 'assessmentsCompleted', 'avgMatchScore',
            'careerPathways', 'successRate', 'studentsWithoutAssessment',
            'pendingSessions', 'upcomingSessions', 'recentSessions',
            'studentsNeedingGuidance', 'chartData'
        ));
    }

    /** List all students with filters */
    public function students(Request $request): View
    {
        $query = User::where('role', 'student')
            ->with('studentProfile');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('status')) {
            if ($request->status === 'no_assessment') {
                $studentsWithAssessment = AssessmentAttempt::where('status', 'completed')
                    ->pluck('student_id');
                $query->whereNotIn('id', $studentsWithAssessment);
            } elseif ($request->status === 'needs_guidance') {
                // Complex filter - handle in PHP or with subquery
            }
        }

        $students = $query->paginate(20);

        // Add status flags
        foreach ($students as $student) {
            $student->has_assessment = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();

            if ($student->has_assessment) {
                $bestMatch = Recommendation::where('student_id', $student->id)
                    ->where('type', 'career')
                    ->max('confidence_score');
                $student->needs_guidance = $bestMatch < 60;
            } else {
                $student->needs_guidance = true;
            }
        }

        return view('dashboard.counsellor.students', compact('students'));
    }

    /** View student details for counselling */
    public function studentDetail(User $user): View
    {
        abort_if($user->role !== 'student', 404);

        $student = $user->load(['studentProfile', 'academicResults.subject']);

        $assessments = AssessmentAttempt::where('student_id', $student->id)
            ->with(['responses.option.question'])
            ->latest()
            ->get();

        $recommendations = Recommendation::where('student_id', $student->id)
            ->with('recommended')
            ->orderByDesc('confidence_score')
            ->get();

        $careerPath = $recommendations->where('type', 'career')->first();

        // Counselling notes (you'd need a CounsellingSession model)
        $counsellingNotes = ActivityLog::where('user_id', $student->id)
            ->where('action', 'counselling_session')
            ->latest()
            ->get();

        $latestAnalysis = AssessmentAnalysis::where('student_id', $student->id)->latest('updated_at')->first();
        $academicReport = $this->academicProgress->getFullReport($student, $latestAnalysis);

        return view('dashboard.counsellor.student-detail', compact(
            'student', 'assessments', 'recommendations', 'careerPath', 'counsellingNotes', 'academicReport'
        ));
    }

    /** Save counselling session notes */
    public function saveSession(Request $request): RedirectResponse
    {
        $request->validate([
            'student_id' => ['required', 'exists:users,id'],
            'notes'      => ['required', 'string'],
        ]);

        $student = User::where('id', $request->student_id)->where('role', 'student')->firstOrFail();

        ActivityLog::record('counselling_session', null, [
            'student_id' => $student->id,
            'notes'      => $request->notes,
        ]);

        return back()->with('success', 'Note sent successfully.');
    }

    /** List all scheduled counselling sessions */
    public function sessions(): View
    {
        $allSessions = CounsellingSession::with(['student'])
            ->where('counsellor_id', Auth::id())
            ->orderByDesc('created_at')
            ->get();

        $pending  = $allSessions->where('status', 'pending_confirmation');
        $upcoming = $allSessions->where('status', 'scheduled')->sortBy('scheduled_at');
        $past     = $allSessions->whereIn('status', ['completed', 'cancelled'])->sortByDesc('scheduled_at');

        $students = User::where('role', 'student')->orderBy('name')->get();

        return view('dashboard.counsellor.sessions', compact('allSessions', 'pending', 'upcoming', 'past', 'students'));
    }

    /** Store a new scheduled session */
    public function storeSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'   => ['nullable', 'integer', 'exists:users,id'],
            'scheduled_at' => ['required', 'date', 'after:' . now()->subMinutes(2)->format('Y-m-d H:i:s')],
            'venue'        => ['nullable', 'string', 'max:255'],
            'message'      => ['nullable', 'string'],
        ]);

        CounsellingSession::create([
            'counsellor_id' => Auth::id(),
            'student_id'    => $data['student_id'] ?: null,
            'scheduled_at'  => $data['scheduled_at'],
            'venue'         => $data['venue'] ?? null,
            'message'       => $data['message'] ?? null,
            'status'        => 'scheduled',
            'initiated_by'  => 'counsellor',
        ]);

        return back()->with('success', 'Session scheduled successfully.');
    }

    /** Cancel a session */
    public function cancelSession(CounsellingSession $session): RedirectResponse
    {
        abort_if($session->counsellor_id !== Auth::id(), 403);
        $session->update(['status' => 'cancelled']);
        return back()->with('success', 'Session cancelled.');
    }

    /** Respond to a student-requested appointment with notes */
    public function respondToSession(Request $request, CounsellingSession $session): RedirectResponse
    {
        abort_if($session->counsellor_id !== Auth::id(), 403);

        $data = $request->validate([
            'counsellor_notes' => ['required', 'string', 'max:1000'],
        ]);

        $session->update(['counsellor_notes' => $data['counsellor_notes']]);
        return back()->with('success', 'Response sent to the student.');
    }

    /** Confirm a pending student appointment (set exact time + venue) */
    public function confirmSession(Request $request, CounsellingSession $session): RedirectResponse
    {
        abort_if($session->counsellor_id !== Auth::id(), 403);

        $data = $request->validate([
            'scheduled_at'     => ['required', 'date', 'after:now'],
            'venue'            => ['nullable', 'string', 'max:255'],
            'counsellor_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $session->update([
            'scheduled_at'     => $data['scheduled_at'],
            'venue'            => $data['venue'],
            'counsellor_notes' => $data['counsellor_notes'],
            'status'           => 'scheduled',
        ]);

        return back()->with('success', 'Appointment confirmed and student notified.');
    }

    /** All student career profiles */
    public function careerProfiles(): View
    {
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'recommendations' => fn($q) => $q->where('type', 'career')->orderByDesc('confidence_score')])
            ->get()
            ->sortByDesc(fn($s) => $s->recommendations->max('confidence_score'));

        return view('dashboard.counsellor.career-profiles', compact('students'));
    }

    /** Analytics dashboard */
    public function analytics(): View
    {
        $allStudents = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations.recommended'])
            ->get();

        $studentIds = $allStudents->pluck('id');

        $completedIds = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->pluck('student_id')
            ->unique();

        $uniEligible = Recommendation::whereIn('student_id', $studentIds)
            ->where('type', 'university_program')
            ->where('confidence_score', '>=', 80)
            ->distinct('student_id')
            ->count('student_id');

        // Career interest distribution for chart
        $careerInterests = $allStudents
            ->flatMap(fn($s) => $s->recommendations->where('type', 'career'))
            ->groupBy(fn($r) => $r->recommended?->category ?? 'Other')
            ->map->count()
            ->toArray();

        // Students needing attention (no assessment or low match)
        $studentsNeedingAttention = $allStudents->filter(function ($student) {
            $done = $student->assessmentAttempts->where('status', 'completed')->isNotEmpty();
            if (!$done) return true;
            $best = $student->recommendations->where('type', 'career')->max('confidence_score');
            return $best < 50;
        })->values();

        $onTrack     = $allStudents->count() - $studentsNeedingAttention->count();
        $needsAttn   = $studentsNeedingAttention->count();

        // Trend: assessments completed per month (last 6 months)
        $trend = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->select(DB::raw('DATE_FORMAT(completed_at, "%b %Y") as month'), DB::raw('count(*) as cnt'))
            ->groupBy('month')
            ->orderByRaw('MIN(completed_at)')
            ->limit(6)
            ->get();

        $analyticsData = [
            'total_students'         => $allStudents->count(),
            'completed_assessments'  => $completedIds->count(),
            'total_career_recs'      => Recommendation::whereIn('student_id', $studentIds)->where('type', 'career')->count(),
            'university_eligible'    => $uniEligible,
            'career_interests'       => $careerInterests ?: ['No data' => 1],
            'on_track'               => $onTrack,
            'needs_attention'        => $needsAttn,
            'trend_labels'           => $trend->pluck('month')->toArray() ?: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'trend_data'             => $trend->pluck('cnt')->toArray()   ?: [0, 0, 0, 0, 0, 0],
        ];

        return view('dashboard.counsellor.analytics', compact('analyticsData', 'studentsNeedingAttention'));
    }

    /** Career library */
    public function careerLibrary(): View
    {
        $careers = Career::with('subjects', 'universityPrograms')->get();

        $categories = $careers->groupBy('category')->keys();

        return view('dashboard.counsellor.careers', compact('careers', 'categories'));
    }

    /** At-Risk Student Alert System — proactive early warning dashboard */
    public function atRisk(): View
    {
        $students = User::where('role', 'student')
            ->with([
                'studentProfile',
                'academicResults',
                'assessmentAttempts' => fn($q) => $q->where('status', 'completed')->latest('completed_at')->limit(1),
                'careerGoal.career',
            ])
            ->get();

        $studentData = $students->map(function ($student) {
            $riskScore  = 0;
            $riskFlags  = [];

            // Flag 1: No assessment completed
            $hasAssessment = $student->assessmentAttempts->isNotEmpty();
            if (!$hasAssessment) {
                $riskScore += 30;
                $riskFlags[] = ['label' => 'No Assessment', 'level' => 'high', 'detail' => 'Has not completed the career self-assessment quiz'];
            }

            // Flag 2: No career goal set
            $hasGoal = (bool) $student->careerGoal?->career_id;
            if (!$hasGoal) {
                $riskScore += 20;
                $riskFlags[] = ['label' => 'No Career Goal', 'level' => 'medium', 'detail' => 'Career goal has not been set'];
            }

            // Flag 3: Low or no academic grades
            $grades   = $student->academicResults;
            $avgScore = $grades->isNotEmpty() ? round($grades->avg('score'), 1) : null;
            if ($avgScore === null) {
                $riskScore += 15;
                $riskFlags[] = ['label' => 'No Grades Uploaded', 'level' => 'medium', 'detail' => 'Teacher has not uploaded any grades'];
            } elseif ($avgScore < 40) {
                $riskScore += 25;
                $riskFlags[] = ['label' => 'Failing Grades', 'level' => 'high', 'detail' => "Average score is {$avgScore}% — below pass mark"];
            } elseif ($avgScore < 55) {
                $riskScore += 10;
                $riskFlags[] = ['label' => 'Struggling Grades', 'level' => 'medium', 'detail' => "Average score is {$avgScore}% — needs improvement"];
            }

            // Flag 4: No recent counselling session in 30+ days
            $lastSession = CounsellingSession::where('student_id', $student->id)
                ->whereIn('status', ['completed', 'confirmed'])
                ->orderByDesc('scheduled_at')
                ->value('scheduled_at');
            if (!$lastSession) {
                $riskScore += 10;
                $riskFlags[] = ['label' => 'No Counselling', 'level' => 'medium', 'detail' => 'Has never attended a counselling session'];
            } elseif (now()->diffInDays($lastSession) > 30) {
                $riskScore += 5;
                $riskFlags[] = ['label' => 'Overdue Session', 'level' => 'low', 'detail' => 'Last session was over 30 days ago'];
            }

            $riskLevel = match(true) {
                $riskScore >= 50 => 'high',
                $riskScore >= 20 => 'medium',
                default          => 'low',
            };

            return [
                'student'   => $student,
                'riskScore' => $riskScore,
                'riskLevel' => $riskLevel,
                'riskFlags' => $riskFlags,
                'avgScore'  => $avgScore,
                'hasGoal'   => $hasGoal,
                'hasAssessment' => $hasAssessment,
            ];
        })->sortByDesc('riskScore')->values();

        $highRisk   = $studentData->where('riskLevel', 'high')->values();
        $mediumRisk = $studentData->where('riskLevel', 'medium')->values();
        $lowRisk    = $studentData->where('riskLevel', 'low')->values();

        $summary = [
            'total'      => $studentData->count(),
            'high'       => $highRisk->count(),
            'medium'     => $mediumRisk->count(),
            'low'        => $lowRisk->count(),
            'no_assessment' => $studentData->filter(fn($d) => !$d['hasAssessment'])->count(),
            'no_goal'       => $studentData->filter(fn($d) => !$d['hasGoal'])->count(),
        ];

        return view('dashboard.counsellor.at-risk', compact(
            'studentData', 'highRisk', 'mediumRisk', 'lowRisk', 'summary'
        ));
    }
}
