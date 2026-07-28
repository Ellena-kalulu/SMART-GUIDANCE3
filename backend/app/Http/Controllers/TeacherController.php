<?php

namespace App\Http\Controllers;

use App\Imports\AcademicResultsImport;
use App\Models\{ActivityLog, AcademicResult, AssessmentAttempt, Recommendation, StudentProfile, Subject, TeacherComment, User};
use App\Services\{AcademicProgressService, CareerPathTrackingService, GradesCsvService};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\{RedirectResponse, Request, Response};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherController extends Controller
{
    public function __construct(
        protected CareerPathTrackingService $pathTracking,
        protected AcademicProgressService $academicProgress,
        protected GradesCsvService $gradesCsv,
    ) {}

    /** Dashboard */
    public function dashboard(): View
    {
        $user = Auth::user();

        $students = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations', 'academicResults.subject'])
            ->get();

        $studentIds = $students->pluck('id');

        $totalStudents        = $students->count();
        $assessmentsCompleted = $students->filter(fn($s) => $s->assessmentAttempts->where('status', 'completed')->isNotEmpty())->count();
        $avgMatchScore        = round(Recommendation::whereIn('student_id', $studentIds)->where('type', 'career')->avg('confidence_score') ?? 0);
        $pendingReviews       = $totalStudents - $assessmentsCompleted;

        // Flag students needing attention
        $studentsNeedingAttention = $students->filter(function ($student) {
            $done = $student->assessmentAttempts->where('status', 'completed')->isNotEmpty();
            if (!$done) return true;
            $best = $student->recommendations->where('type', 'career')->max('confidence_score') ?? 0;
            if ($best < 60) return true;
            $avg = $student->academicResults->avg('score');
            return $avg !== null && $avg < 50;
        });

        $attentionCount = $studentsNeedingAttention->count();

        
        $interventions = [];
        foreach ($students as $student) {
            $items = $this->academicProgress->getTeacherInterventions($student);
            foreach ($items as $item) {
                $interventions[] = $item;
            }
        }
        $interventions = array_slice($interventions, 0, 8);
        $recentStudents = $students->sortByDesc(fn($s) => $s->assessmentAttempts->max('created_at'))->take(5);

        return view('dashboard.teacher.index', compact(
            'user', 'totalStudents', 'assessmentsCompleted', 'avgMatchScore',
            'pendingReviews', 'studentsNeedingAttention', 'attentionCount', 'recentStudents', 'interventions'
        ));
    }

    /** List all students */
    public function students(Request $request): View
    {
        $query = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations.recommended', 'academicResults']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('form')) {
            $query->whereHas('studentProfile', fn($q) => $q->where('form_level', $request->form));
        }

        $students = $query->paginate(20);

        foreach ($students as $student) {
            $done      = $student->assessmentAttempts->where('status', 'completed')->isNotEmpty();
            $topCareer = $student->recommendations->where('type', 'career')->sortByDesc('confidence_score')->first();
            $best      = $topCareer?->confidence_score ?? 0;
            $avg       = $student->academicResults->avg('score');

            $student->assessment_completed = $done;
            $student->top_career           = $topCareer;

            $reasons = [];
            if (!$done)                              $reasons[] = 'No assessment';
            if ($done && $best < 60)                 $reasons[] = 'No clear path';
            if ($avg !== null && $avg < 50)          $reasons[] = 'Poor performance';
            $student->attention_flags = $reasons;
        }

        $attentionCount = $students->getCollection()->filter(fn($s) => count($s->attention_flags) > 0)->count();

        $forms = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];

        return view('dashboard.teacher.students', compact('students', 'forms', 'attentionCount'));
    }

    /** View student details */
    public function studentDetail(User $user): View
    {
        abort_if($user->role !== 'student', 404);

        $student = $user->load(['studentProfile', 'academicResults.subject']);

        $assessments = AssessmentAttempt::where('student_id', $student->id)
            ->with('responses.option')
            ->latest()
            ->get();

        $recommendations = Recommendation::where('student_id', $student->id)
            ->with(['recommended.subjects'])
            ->orderByDesc('confidence_score')
            ->get();

        $careers = $recommendations->where('type', 'career');
        $universities = $recommendations->where('type', 'university_program');

        $comments = TeacherComment::where('student_id', $student->id)
            ->with('teacher')
            ->latest()
            ->get();

        // Attention flags
        $hasAssessment = $assessments->where('status', 'completed')->isNotEmpty();
        $bestScore     = $careers->max('confidence_score') ?? 0;
        $avgAcademic   = $student->academicResults->avg('score') ?? null;

        $flags = [];
        if (!$hasAssessment)            $flags[] = 'Assessment not completed';
        if ($hasAssessment && $bestScore < 60) $flags[] = 'No clear career path (best match ' . round($bestScore) . '%)';
        if ($avgAcademic !== null && $avgAcademic < 50) $flags[] = 'Poor academic performance (' . round($avgAcademic) . '% avg)';

        $recommendationHistory = app(\App\Services\IntelligentAnalysisService::class)
            ->getRecommendationHistory($student);

        return view('dashboard.teacher.student-detail', compact(
            'student', 'assessments', 'recommendations', 'careers', 'universities', 'comments', 'flags', 'recommendationHistory'
        ));
    }

    /** Store a teacher comment on a student */
    public function storeComment(Request $request, User $user): RedirectResponse
    {
        abort_if($user->role !== 'student', 404);

        $request->validate(['comment' => ['required', 'string', 'max:1000']]);

        TeacherComment::create([
            'teacher_id' => Auth::id(),
            'student_id' => $user->id,
            'comment'    => $request->comment,
        ]);

        return back()->with('success', 'Report comment added successfully.');
    }

    /** Generate reports */
    public function reports(): View
    {
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations.recommended', 'academicResults.subject'])
            ->get();

        $studentIds = $students->pluck('id');

        $completed = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');

        $reportData = [
            'total_students'        => $students->count(),
            'completed_assessments' => $completed,
            'pending_assessments'   => $students->count() - $completed,
            'avg_career_match'      => round(
                Recommendation::whereIn('student_id', $studentIds)->where('type', 'career')->avg('confidence_score') ?? 0
            ),
        ];

        $summary = $reportData + [
            'top_careers' => Recommendation::whereIn('student_id', $studentIds)
                ->where('type', 'career')
                ->select('recommended_id', DB::raw('count(*) as count'))
                ->groupBy('recommended_id')
                ->with('recommended')
                ->orderByDesc('count')
                ->limit(5)
                ->get(),
        ];

        $formDistribution = StudentProfile::whereIn('user_id', $studentIds)
            ->select('form_level', DB::raw('count(*) as count'))
            ->groupBy('form_level')
            ->get();

        return view('dashboard.teacher.reports', compact('students', 'summary', 'reportData', 'formDistribution'));
    }

    /** Export all students as PDF or CSV */
    public function exportReport(Request $request): mixed
    {
        $format = $request->input('format', 'pdf');

        $students = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'recommendations.recommended', 'academicResults.subject'])
            ->get();

        $studentIds = $students->pluck('id');
        $completed  = AssessmentAttempt::whereIn('student_id', $studentIds)
            ->where('status', 'completed')->distinct('student_id')->count('student_id');

        $summary = [
            'total'             => $students->count(),
            'with_assessment'   => $completed,
            'avg_career_match'  => round(
                Recommendation::whereIn('student_id', $studentIds)->where('type', 'career')->avg('confidence_score') ?? 0
            ),
        ];

        ActivityLog::record('report_exported');

        if ($format === 'csv') {
            $filename = 'students-report-' . now()->format('Y-m-d') . '.csv';
            $headers  = [
                'Content-Type'        => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function () use ($students) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['#', 'Name', 'Student No.', 'Form', 'Stream', 'Avg Score', 'Assessment', 'Top Career', 'Uni Eligible']);
                foreach ($students as $i => $s) {
                    $done    = $s->assessmentAttempts->where('status', 'completed')->isNotEmpty();
                    $topRec  = $s->recommendations->where('type', 'career')->sortByDesc('confidence_score')->first();
                    $uniElig = $s->recommendations->where('type', 'university_program')->where('confidence_score', '>=', 80)->count();
                    $avg     = $s->academicResults->avg('score');
                    fputcsv($handle, [
                        $i + 1,
                        $s->name,
                        $s->studentProfile?->student_number ?? '',
                        $s->studentProfile?->form_level ?? '',
                        $s->studentProfile?->stream ?? '',
                        $avg ? round($avg) . '%' : '',
                        $done ? 'Completed' : 'Pending',
                        $topRec?->recommended?->title ?? '',
                        $uniElig > 0 ? "{$uniElig} programme(s)" : '',
                    ]);
                }
                fclose($handle);
            };

            return response()->streamDownload($callback, $filename, $headers);
        }

        // Default: PDF
        $pdf = Pdf::loadView('pdf.bulk-report', compact('students', 'summary'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('students-report-' . now()->format('Y-m-d') . '.pdf');
    }

    /** Download individual student report card as PDF */
    public function downloadStudentReport(User $user): mixed
    {
        abort_if($user->role !== 'student', 404);

        $student = $user->load(['studentProfile', 'assessmentAttempts.responses.option', 'recommendations.recommended', 'academicResults.subject']);

        ActivityLog::record('student_report_downloaded', $student);

        $pdf = Pdf::loadView('pdf.student-report-card', compact('student'))
            ->setPaper('a4', 'portrait');

        $name = str_replace(' ', '-', strtolower($student->name));
        return $pdf->download("report-card-{$name}-" . now()->format('Y-m-d') . '.pdf');
    }

    /** View all student grades with filters */
    public function grades(Request $request): View
    {
        $formLevel  = $request->input('form_level');
        $term       = $request->input('term');
        $subjectId  = $request->input('subject_id');
        $search     = $request->input('search');

        $query = AcademicResult::with(['student.studentProfile', 'subject'])
            ->orderBy('form_level')
            ->orderBy('term')
            ->orderBy('created_at', 'desc');

        if ($formLevel) {
            $query->where('form_level', $formLevel);
        }
        if ($term) {
            $query->where('term', $term);
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }
        if ($search) {
            $query->whereHas('student', fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhereHas('studentProfile', fn($q2) => $q2->where('student_number', 'like', "%{$search}%")));
        }

        $results = $query->paginate(30)->withQueryString();

        $subjects = Subject::orderBy('name')->get();
        $terms    = ['Term 1', 'Term 2', 'Term 3'];
        $forms    = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];

        // Summary stats for current filter
        $filteredIds = $query->toBase()->pluck('id');
        $stats = [
            'total'   => $results->total(),
            'avg'     => round(AcademicResult::whereIn('id', $filteredIds)->avg('score') ?? 0, 1),
            'highest' => round(AcademicResult::whereIn('id', $filteredIds)->max('score') ?? 0, 1),
            'lowest'  => round(AcademicResult::whereIn('id', $filteredIds)->min('score') ?? 0, 1),
        ];

        return view('dashboard.teacher.grades', compact(
            'results', 'subjects', 'terms', 'forms',
            'formLevel', 'term', 'subjectId', 'search', 'stats'
        ));
    }

    /** Redirect legacy import URL to grades page */
    public function importGradesForm(): RedirectResponse
    {
        return redirect()->to(route('teacher.grades') . '#upload');
    }

    /** Process uploaded Excel grade sheet */
    public function importGrades(Request $request): RedirectResponse
    {
        $request->validate([
            'file'       => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
            'term'       => ['required', 'string'],
            'form_level' => ['required', 'string'],
        ]);

        $import = new AcademicResultsImport(
            term:      $request->input('term'),
            formLevel: $request->input('form_level'),
        );

        Excel::import($import, $request->file('file'));

        $affectedIds = array_values($import->affectedStudentIds);
        if (!empty($affectedIds)) {
            $this->pathTracking->refreshAnalysisForStudents($affectedIds);
            $this->pathTracking->refreshGoalsForStudents($affectedIds);
        }

        ActivityLog::record('grades_imported', null, [
            'term'       => $request->input('term'),
            'form_level' => $request->input('form_level'),
            'imported'   => count($import->imported),
            'skipped'    => count($import->skipped),
        ]);

        $msg = count($import->imported) . ' grade records imported successfully.';
        if (count($import->skipped)) {
            $msg .= ' ' . count($import->skipped) . ' row(s) skipped - check the warnings below.';
        }

        // Group repeated skip reasons together so a large file doesn't dump
        // hundreds of near-identical lines on the teacher.
        $skipSummary = collect($import->skipped)
            ->map(function ($reason) {
                return match (true) {
                    str_contains($reason, 'no score entered')  => 'Missing score - type a score in the Score column for these rows',
                    str_contains($reason, 'missing student number') => 'Missing student number',
                    str_contains($reason, 'missing subject')    => 'Missing subject',
                    str_contains($reason, 'not found')          => str_contains($reason, "Student number") ? 'Student number not recognised' : 'Subject not recognised',
                    str_contains($reason, 'out of range')       => 'Score out of range (must be 0-100)',
                    default                                     => 'Other',
                };
            })
            ->countBy()
            ->sortDesc()
            ->map(fn ($count, $reason) => "{$reason}: {$count} row(s)")
            ->values()
            ->all();

        return redirect()->route('teacher.grades')
            ->with('success', $msg)
            ->with('import_skip_summary', $skipSummary)
            ->with('import_skipped', array_slice($import->skipped, 0, 20))
            ->with('import_skipped_total', count($import->skipped))
            ->with('import_imported', $import->imported);
    }


    /** Download blank CSV template for grade import */
    public function downloadGradesTemplate(): StreamedResponse
    {
        return $this->gradesCsv->downloadTemplate();
    }

    /** Download a ready-to-fill template for one form: every student x every subject, blank scores */
    public function downloadBlankFormTemplate(Request $request): StreamedResponse
    {
        $request->validate([
            'form_level' => ['required', 'in:Form 1,Form 2,Form 3,Form 4'],
        ]);

        return $this->gradesCsv->downloadBlankFormTemplate($request->input('form_level'));
    }

    /** Export all student grades currently in the system */
    public function exportAllGrades(): StreamedResponse
    {
        return $this->gradesCsv->exportAllGrades();
    }

    /** Export grades for a specific form (for re-upload) */
    public function exportFormGrades(Request $request): StreamedResponse
    {
        $request->validate([
            'form_level' => ['required', 'in:Form 1,Form 2,Form 3,Form 4'],
            'term'       => ['nullable', 'string'],
        ]);

        return $this->gradesCsv->exportFormCsv(
            $request->input('form_level'),
            $request->input('term', 'Term 3')
        );
    }

    /** Analytics dashboard */
    public function analytics(): View
    {
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'assessmentAttempts', 'academicResults.subject', 'recommendations.recommended'])
            ->get();

        $studentIds = $students->pluck('id');

        // Career interest distribution: category => count
        $careerInterests = $students
            ->flatMap(fn($s) => $s->recommendations->where('type', 'career'))
            ->groupBy(fn($r) => $r->recommended?->category ?? 'Other')
            ->map->count()
            ->toArray();

        // Average score per subject
        $subjectAverages = $students
            ->flatMap->academicResults
            ->groupBy(fn($r) => $r->subject?->name ?? 'Unknown')
            ->map(fn($g) => round($g->avg('score'), 1))
            ->sortKeysDesc()
            ->take(8)
            ->toArray();

        $analyticsData = [
            'career_interests' => $careerInterests,
            'subject_averages' => $subjectAverages,
        ];

        return view('dashboard.teacher.analytics', compact('students', 'analyticsData'));
    }
}

