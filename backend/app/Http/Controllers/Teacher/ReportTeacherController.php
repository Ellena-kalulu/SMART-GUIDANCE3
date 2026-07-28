<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{AcademicResult, ActivityLog, AssessmentAttempt, Career, Recommendation, StudentProfile, UniversityProgram, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;
use Illuminate\View\View;

class ReportTeacherController extends Controller
{
    /**
     * Main Reports Dashboard for Teacher
     */
    public function index(Request $request): View|Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $reportType = $request->input('report_type', 'class_performance');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $formLevel = $request->input('form_level');
        $stream = $request->input('stream');
        $format = $request->input('format', 'html');

        if ($reportType) {
            session(['teacher_last_report' => $reportType]);
        }

        $data = null;
        if ($reportType && $reportType !== '') {
            $data = $this->getReportData($reportType, $dateFrom, $dateTo, $formLevel, $stream);
        }

        $filterOptions = $this->getFilterOptions();

        if ($format !== 'html' && $data) {
            return $this->exportReport($reportType, $data, $format, $dateFrom, $dateTo);
        }

        return view('teacher.reports.index', compact(
            'reportType', 'data', 'dateFrom', 'dateTo', 'formLevel',
            'stream', 'filterOptions'
        ));
    }

    /**
     * Report #11: Class Performance Overview
     */
    public function classPerformance(Request $request): View|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $formLevel = $request->input('form_level', 'Form 4');
        $stream = $request->input('stream');

        $students = $this->getStudentsByClass($formLevel, $stream);
        $performanceData = $this->calculateClassPerformance($students);

        if ($request->input('format') === 'csv') {
            return $this->exportClassPerformanceCSV($performanceData);
        }

        return view('teacher.reports.class-performance', compact('performanceData', 'formLevel', 'stream'));
    }

    /**
     * Report #12: Individual Student Report Card
     */
    public function studentReportCard(int $studentId, Request $request)
    {
        $student = User::where('role', 'student')
            ->with(['studentProfile', 'academicResults.subject', 'recommendations.recommended'])
            ->findOrFail($studentId);

        $assessments = AssessmentAttempt::where('student_id', $studentId)
            ->with('responses.option')
            ->latest()
            ->get();

        $topCareers = $student->recommendations
            ->where('type', 'career')
            ->sortByDesc('confidence_score')
            ->take(5);

        $universities = $student->recommendations
            ->where('type', 'university_program')
            ->where('confidence_score', '>=', 70)
            ->sortByDesc('confidence_score');

        $academicSummary = $this->getAcademicSummary($student);

        $pdf = PDF::loadView('teacher.reports.pdf.student-report-card', compact(
            'student', 'assessments', 'topCareers', 'universities', 'academicSummary'
        ));

        return $pdf->download('student-report-card-' . $student->name . '.pdf');
    }

    /**
     * Report #13: Bulk Student Progress Report
     */
    public function bulkProgress(Request $request)
    {
        $formLevel = $request->input('form_level');
        $stream = $request->input('stream');

        $students = $this->getStudentsByClass($formLevel, $stream);
        $progressData = [];

        foreach ($students as $student) {
            $hasAssessment = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();

            $topCareer = $student->recommendations
                ->where('type', 'career')
                ->sortByDesc('confidence_score')
                ->first();

            $academicAvg = $student->academicResults()->avg('score') ?? 0;

            $progressData[] = [
                'name' => $student->name,
                'student_number' => $student->studentProfile->student_number ?? 'N/A',
                'form' => $student->studentProfile->form_level ?? 'N/A',
                'stream' => $student->studentProfile->stream ?? 'N/A',
                'assessment_status' => $hasAssessment ? 'Completed' : 'Pending',
                'top_career' => $topCareer?->recommended?->title ?? 'N/A',
                'match_score' => round($topCareer?->confidence_score ?? 0, 1),
                'academic_average' => round($academicAvg, 1) . '%',
                'recommendations_count' => $student->recommendations->count(),
            ];
        }

        $format   = $request->input('format', 'html');
        $filename = 'bulk-progress-report-' . now()->format('Y-m-d');

        if ($format === 'csv') {
            return $this->exportToCSV($progressData, $filename);
        }

        if ($format === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.bulk-progress', compact('progressData', 'formLevel', 'stream'));
            return $pdf->download($filename . '.pdf');
        }

        return view('teacher.reports.bulk-progress', compact('progressData', 'formLevel', 'stream'));
    }

    /**
     * Report #14: Students Needing Attention
     */
    public function studentsNeedingAttention(Request $request): View|Response
    {
        $students = $this->getStudentsNeedingAttention();

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.attention-list', compact('students'));
            return $pdf->download('students-needing-attention.pdf');
        }

        return view('teacher.reports.attention-list', compact('students'));
    }

    /**
     * Report #15: Career Interest Distribution
     */
    public function careerInterestDistribution(Request $request): View|Response
    {
        $formLevel = $request->input('form_level');
        $stream = $request->input('stream');

        $distribution = $this->getCareerInterestDistribution($formLevel, $stream);

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.career-distribution', compact('distribution'));
            return $pdf->download('career-interest-distribution.pdf');
        }

        return view('teacher.reports.career-distribution', compact('distribution'));
    }

    /**
     * Report #16: Assessment Completion Trend
     */
    public function assessmentTrend(Request $request): View|Response
    {
        $formLevel = $request->input('form_level');
        $trendData = $this->getAssessmentTrendData($formLevel);

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.assessment-trend', compact('trendData'));
            return $pdf->download('assessment-completion-trend.pdf');
        }

        return view('teacher.reports.assessment-trend', compact('trendData'));
    }

    /**
     * Report #17: Subject Performance Analysis
     */
    public function subjectPerformance(Request $request): View|Response
    {
        $formLevel = $request->input('form_level', 'Form 4');
        $stream = $request->input('stream');

        $subjectData = $this->getSubjectPerformanceData($formLevel, $stream);

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.subject-performance', compact('subjectData'));
            return $pdf->download('subject-performance-analysis.pdf');
        }

        return view('teacher.reports.subject-performance', compact('subjectData'));
    }

    /**
     * Report #18: Form Level Comparison
     */
    public function formComparison(Request $request): View|Response
    {
        $comparisonData = $this->getFormComparisonData();

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.form-comparison', compact('comparisonData'));
            return $pdf->download('form-level-comparison.pdf');
        }

        return view('teacher.reports.form-comparison', compact('comparisonData'));
    }

    /**
     * Report #19: Recommendation Acceptance Rate
     */
    public function recommendationAcceptance(Request $request): View|Response
    {
        $acceptanceData = $this->getRecommendationAcceptanceData();

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.recommendation-acceptance', compact('acceptanceData'));
            return $pdf->download('recommendation-acceptance.pdf');
        }

        return view('teacher.reports.recommendation-acceptance', compact('acceptanceData'));
    }

    /**
     * Report #20: University Placement Prediction
     */
    public function universityPlacement(Request $request): View|Response
    {
        $formLevel = $request->input('form_level', 'Form 4');
        $predictions = $this->getUniversityPlacementPredictions($formLevel);

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.placement-prediction', compact('predictions'));
            return $pdf->download('university-placement-prediction.pdf');
        }

        return view('teacher.reports.placement-prediction', compact('predictions'));
    }

    /**
     * Report #21: Parent-Teacher Conference Report
     */
    public function parentConferenceReport(int $studentId, Request $request)
    {
        $student = User::where('role', 'student')
            ->with(['studentProfile', 'academicResults.subject', 'recommendations.recommended'])
            ->findOrFail($studentId);

        $highlights = $this->getStudentHighlights($student);
        $concerns = $this->getStudentConcerns($student);
        $recommendations = $this->getParentRecommendations($student);

        $pdf = PDF::loadView('teacher.reports.pdf.parent-conference', compact(
            'student', 'highlights', 'concerns', 'recommendations'
        ));

        return $pdf->download('parent-conference-report-' . $student->name . '.pdf');
    }

    /**
     * Report #22: Intervention Tracking Report
     */
    public function interventionTracking(Request $request): View|Response
    {
        $interventions = ActivityLog::where('action', 'counselling_session')
            ->with('user')
            ->latest()
            ->get();

        if ($request->input('format') === 'pdf') {
            $pdf = PDF::loadView('teacher.reports.pdf.intervention-tracking', compact('interventions'));
            return $pdf->download('intervention-tracking-report.pdf');
        }

        return view('teacher.reports.intervention-tracking', compact('interventions'));
    }

    /**
     * Report #23: Weekly Progress Summary (Email/Scheduled)
     */
    public function weeklyProgressSummary(Request $request)
    {
        $formLevel = $request->input('form_level');
        $summary = $this->generateWeeklySummary($formLevel);

        return view('teacher.reports.weekly-summary', compact('summary'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Data Collection Methods
    // ──────────────────────────────────────────────────────────────────────────

    private function getReportData($reportType, $dateFrom, $dateTo, $formLevel, $stream)
    {
        return match($reportType) {
            'class_performance' => $this->getClassPerformanceData($formLevel, $stream),
            'attention_list' => ['title' => 'Students Needing Attention', 'students' => $this->getStudentsNeedingAttention()],
            'career_distribution' => $this->getCareerInterestDistribution($formLevel, $stream),
            'assessment_trend' => $this->getAssessmentTrendData($formLevel),
            'subject_performance' => $this->getSubjectPerformanceData($formLevel, $stream),
            'form_comparison' => $this->getFormComparisonData(),
            'recommendation_acceptance' => $this->getRecommendationAcceptanceData(),
            'placement_prediction' => $this->getUniversityPlacementPredictions($formLevel),
            'intervention_tracking' => ['title' => 'Intervention Tracking', 'interventions' => ActivityLog::where('action', 'counselling_session')->latest()->get()],
            default => $this->getClassPerformanceData($formLevel, $stream),
        };
    }

    private function getClassPerformanceData($formLevel, $stream)
    {
        $students = $this->getStudentsByClass($formLevel, $stream);
        return $this->calculateClassPerformance($students);
    }

    private function getStudentsByClass($formLevel, $stream)
    {
        $query = User::where('role', 'student')
            ->with(['studentProfile', 'recommendations', 'academicResults']);

        if ($formLevel) {
            $query->whereHas('studentProfile', fn($q) => $q->where('form_level', $formLevel));
        }
        if ($stream) {
            $query->whereHas('studentProfile', fn($q) => $q->where('stream', $stream));
        }

        return $query->get();
    }

    private function calculateClassPerformance($students)
    {
        $total = $students->count();
        $assessmentsCompleted = 0;
        $totalMatchScore = 0;
        $totalAcademicAvg = 0;

        foreach ($students as $student) {
            $hasAssessment = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();
            if ($hasAssessment) $assessmentsCompleted++;

            $bestMatch = $student->recommendations
                ->where('type', 'career')
                ->max('confidence_score') ?? 0;
            $totalMatchScore += $bestMatch;

            $totalAcademicAvg += $student->academicResults()->avg('score') ?? 0;
        }

        return [
            'total_students' => $total,
            'completion_rate' => $total > 0 ? round(($assessmentsCompleted / $total) * 100, 1) : 0,
            'avg_match_score' => $total > 0 ? round($totalMatchScore / $total, 1) : 0,
            'avg_academic_score' => $total > 0 ? round($totalAcademicAvg / $total, 1) : 0,
            'students' => $students,
        ];
    }

    private function getStudentsNeedingAttention()
    {
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'recommendations'])
            ->get();

        $needingAttention = [];

        foreach ($students as $student) {
            $hasAssessment = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();

            $bestMatch = $student->recommendations
                ->where('type', 'career')
                ->max('confidence_score') ?? 0;

            if (!$hasAssessment || $bestMatch < 60) {
                $needingAttention[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'form' => $student->studentProfile->form_level ?? 'N/A',
                    'stream' => $student->studentProfile->stream ?? 'N/A',
                    'has_assessment' => $hasAssessment,
                    'match_score' => round($bestMatch, 1),
                    'reason' => !$hasAssessment ? 'No assessment completed' : 'Low match score (' . round($bestMatch, 1) . '%)',
                ];
            }
        }

        return $needingAttention;
    }

    private function getCareerInterestDistribution($formLevel, $stream)
    {
        $students = $this->getStudentsByClass($formLevel, $stream);
        $distribution = [];

        foreach ($students as $student) {
            $topCareer = $student->recommendations
                ->where('type', 'career')
                ->sortByDesc('confidence_score')
                ->first();

            if ($topCareer && $topCareer->recommended) {
                $category = $topCareer->recommended->category ?? 'Other';
                $distribution[$category] = ($distribution[$category] ?? 0) + 1;
            }
        }

        return [
            'distribution' => $distribution,
            'total_students' => $students->count(),
        ];
    }

    private function getAssessmentTrendData($formLevel)
    {
        $query = AssessmentAttempt::where('status', 'completed');

        if ($formLevel) {
            $query->whereHas('student.studentProfile', fn($q) => $q->where('form_level', $formLevel));
        }

        $monthlyData = $query->select(DB::raw('DATE_FORMAT(completed_at, "%b %Y") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderByRaw('MIN(completed_at)')
            ->limit(6)
            ->get();

        return [
            'labels' => $monthlyData->pluck('month'),
            'data' => $monthlyData->pluck('count'),
        ];
    }

    private function getSubjectPerformanceData($formLevel, $stream)
    {
        $students = $this->getStudentsByClass($formLevel, $stream);
        $studentIds = $students->pluck('id');

        $subjectData = AcademicResult::whereIn('student_id', $studentIds)
            ->with('subject')
            ->get()
            ->groupBy('subject_id')
            ->map(function($results) {
                $subject = $results->first()->subject;
                return [
                    'name' => $subject->name ?? 'Unknown',
                    'avg_score' => round($results->avg('score'), 1),
                    'highest_score' => round($results->max('score'), 1),
                    'lowest_score' => round($results->min('score'), 1),
                    'students_count' => $results->groupBy('student_id')->count(),
                ];
            })
            ->sortByDesc('avg_score')
            ->values();

        return $subjectData;
    }

    private function getFormComparisonData()
    {
        $forms = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];
        $comparison = [];

        foreach ($forms as $form) {
            $students = $this->getStudentsByClass($form, null);
            $performance = $this->calculateClassPerformance($students);

            $comparison[$form] = [
                'total_students' => $performance['total_students'],
                'completion_rate' => $performance['completion_rate'],
                'avg_match_score' => $performance['avg_match_score'],
                'avg_academic_score' => $performance['avg_academic_score'],
            ];
        }

        return $comparison;
    }

    private function getRecommendationAcceptanceData()
    {
        $total = Recommendation::count();
        $accepted = Recommendation::where('status', 'accepted')->count();
        $dismissed = Recommendation::where('status', 'dismissed')->count();
        $pending = Recommendation::where('status', 'pending')->count();

        return [
            'total' => $total,
            'accepted' => $accepted,
            'dismissed' => $dismissed,
            'pending' => $pending,
            'acceptance_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0,
        ];
    }

    private function getUniversityPlacementPredictions($formLevel)
    {
        $students = $this->getStudentsByClass($formLevel, null);
        $predictions = [];

        foreach ($students as $student) {
            $academicAvg = $student->academicResults()->avg('score') ?? 0;
            $eligiblePrograms = UniversityProgram::where('minimum_points', '<=', $academicAvg)
                ->take(3)
                ->get();

            $predictions[] = [
                'student_name' => $student->name,
                'academic_avg' => round($academicAvg, 1),
                'eligible_programs' => $eligiblePrograms->pluck('name')->implode(', '),
                'top_university' => $eligiblePrograms->first()?->university ?? 'N/A',
                'placement_likelihood' => $academicAvg >= 70 ? 'High' : ($academicAvg >= 50 ? 'Medium' : 'Low'),
            ];
        }

        return $predictions;
    }

    private function getStudentHighlights($student)
    {
        $highlights = [];

        $bestCareer = $student->recommendations
            ->where('type', 'career')
            ->sortByDesc('confidence_score')
            ->first();

        if ($bestCareer) {
            $highlights[] = "Strong career match in {$bestCareer->recommended->title} with {$bestCareer->confidence_score}% match";
        }

        $bestSubjects = $student->academicResults()
            ->orderByDesc('score')
            ->with('subject')
            ->take(3)
            ->get();

        foreach ($bestSubjects as $subject) {
            $highlights[] = "Excellent performance in {$subject->subject->name} - {$subject->score}%";
        }

        return $highlights;
    }

    private function getStudentConcerns($student)
    {
        $concerns = [];

        $hasAssessment = AssessmentAttempt::where('student_id', $student->id)
            ->where('status', 'completed')
            ->exists();

        if (!$hasAssessment) {
            $concerns[] = "Career assessment not yet completed";
        }

        $weakSubjects = $student->academicResults()
            ->orderBy('score')
            ->with('subject')
            ->take(3)
            ->get();

        foreach ($weakSubjects as $subject) {
            if ($subject->score < 50) {
                $concerns[] = "Below average performance in {$subject->subject->name} - {$subject->score}%";
            }
        }

        return $concerns;
    }

    private function getParentRecommendations($student)
    {
        $recommendations = [];

        $bestCareer = $student->recommendations
            ->where('type', 'career')
            ->sortByDesc('confidence_score')
            ->first();

        if ($bestCareer) {
            $recommendations[] = "Encourage exploration of {$bestCareer->recommended->title} career path";
        }

        $weakSubjects = $student->academicResults()
            ->where('score', '<', 50)
            ->with('subject')
            ->get();

        foreach ($weakSubjects as $subject) {
            $recommendations[] = "Provide additional support in {$subject->subject->name}";
        }

        return $recommendations;
    }

    private function generateWeeklySummary($formLevel)
    {
        $students = $this->getStudentsByClass($formLevel, null);
        $performance = $this->calculateClassPerformance($students);
        $newAssessments = AssessmentAttempt::where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(7))
            ->count();

        return [
            'week' => now()->format('F j, Y'),
            'form_level' => $formLevel ?? 'All Forms',
            'total_students' => $performance['total_students'],
            'completion_rate' => $performance['completion_rate'],
            'avg_match_score' => $performance['avg_match_score'],
            'new_assessments' => $newAssessments,
            'students_needing_attention' => count($this->getStudentsNeedingAttention()),
        ];
    }

    private function getFilterOptions()
    {
        return [
            'form_levels' => ['Form 1', 'Form 2', 'Form 3', 'Form 4'],
            'streams' => ['Sciences', 'Humanities', 'Languages', 'Commerce', 'General'],
            'report_types' => [
                ['value' => 'class_performance', 'label' => '📊 Class Performance Overview', 'description' => 'Summary of all students with completion rates and match scores'],
                ['value' => 'attention_list', 'label' => '⚠️ Students Needing Attention', 'description' => 'Students with no assessment or low match scores'],
                ['value' => 'career_distribution', 'label' => '🎯 Career Interest Distribution', 'description' => 'Career category interests across the class'],
                ['value' => 'assessment_trend', 'label' => '📈 Assessment Completion Trend', 'description' => 'Monthly assessment completion trends'],
                ['value' => 'subject_performance', 'label' => '📚 Subject Performance Analysis', 'description' => 'Average scores per subject with trends'],
                ['value' => 'form_comparison', 'label' => '📊 Form Level Comparison', 'description' => 'Performance metrics across Form 1-4'],
                ['value' => 'recommendation_acceptance', 'label' => '✅ Recommendation Acceptance Rate', 'description' => 'Statistics on recommendation acceptance'],
                ['value' => 'placement_prediction', 'label' => '🎓 University Placement Prediction', 'description' => 'University eligibility forecasts'],
                ['value' => 'intervention_tracking', 'label' => '📋 Intervention Tracking', 'description' => 'Counselling interventions and outcomes'],
            ],
        ];
    }

    private function getAcademicSummary($student)
    {
        $results = $student->academicResults;
        return [
            'average' => round($results->avg('score'), 1),
            'highest' => round($results->max('score'), 1),
            'lowest' => round($results->min('score'), 1),
            'total_subjects' => $results->groupBy('subject_id')->count(),
        ];
    }

    private function exportReport($reportType, $data, $format, $dateFrom, $dateTo)
    {
        $filename = $reportType . '_' . now()->format('Y-m-d_His');
        // Convert underscore report_type to hyphenated view file name
        $viewSlug = str_replace('_', '-', $reportType);

        $viewData = is_array($data) ? $data : ['data' => $data];
        $viewData = array_merge($viewData, [
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'generatedBy' => auth()->user()->name,
            'reportType'  => $reportType,
        ]);

        $pdf = PDF::loadView('teacher.reports.pdf.' . $viewSlug, $viewData);
        return $pdf->download($filename . '.pdf');
    }

    private function exportToCSV($data, $filename)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function() use ($data) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if (!empty($data)) {
                fputcsv($handle, array_keys($data[0]));
                foreach ($data as $row) {
                    fputcsv($handle, $row);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportClassPerformanceCSV($data)
    {
        $filename = 'class-performance-' . now()->format('Y-m-d');
        $exportData = [];

        foreach ($data['students'] as $student) {
            $exportData[] = [
                'Student Name' => $student->name,
                'Form' => $student->studentProfile->form_level ?? 'N/A',
                'Stream' => $student->studentProfile->stream ?? 'N/A',
                'Assessment Status' => AssessmentAttempt::where('student_id', $student->id)->where('status', 'completed')->exists() ? 'Completed' : 'Pending',
                'Match Score' => round($student->recommendations->where('type', 'career')->max('confidence_score') ?? 0, 1) . '%',
            ];
        }

        return $this->exportToCSV($exportData, $filename);
    }
}
