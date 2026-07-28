<?php

namespace App\Http\Controllers\Counsellor;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, AssessmentAttempt, Career, Recommendation, StudentProfile, UniversityProgram, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReportCounsellorController extends Controller
{
    /**
     * Main Reports Dashboard for Counsellor
     */
    public function index(Request $request): View
    {
        $reportType = $request->input('report_type', 'career_analytics');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $formLevel = $request->input('form_level');
        $stream = $request->input('stream');
        $category = $request->input('category');
        $format = $request->input('format', 'html');

        // Store last report type
        if ($reportType) {
            session(['counsellor_last_report' => $reportType]);
        }

        $data = null;
        if ($reportType && $reportType !== '') {
            $data = $this->getReportData($reportType, $dateFrom, $dateTo, $formLevel, $stream, $category);
        }

        $filterOptions = $this->getFilterOptions();

        if ($format !== 'html' && $data) {
            return $this->exportReport($reportType, $data, $format, $dateFrom, $dateTo);
        }

        return view('counsellor.reports.index', compact(
            'reportType', 'data', 'dateFrom', 'dateTo', 'formLevel',
            'stream', 'category', 'filterOptions'
        ));
    }

    /** School-Wide Career Analytics (Report #24) */
    public function careerAnalytics(Request $request)
    {
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');
        $formLevel = $request->input('form_level');
        $stream    = $request->input('stream');
        $data      = $this->getCareerAnalyticsData($dateFrom, $dateTo, $formLevel, $stream);

        $export = $this->maybeExport($request, 'career_analytics', $data);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.career-analytics', compact('data', 'dateFrom', 'dateTo'));
    }

    /** Student Intervention Priority List (Report #25) */
    public function interventionList(Request $request)
    {
        $students = $this->getInterventionStudents();

        $export = $this->maybeExport($request, 'intervention_list', $students);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.intervention-list', compact('students'));
    }

    /** Counselling Session Log (Report #26) */
    public function sessionLog(Request $request)
    {
        $sessions = ActivityLog::where('action', 'counselling_session')
            ->with('user')
            ->latest()
            ->paginate(20);

        $export = $this->maybeExport($request, 'session_log', $this->getSessionLogData($request->input('date_from'), $request->input('date_to')));
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.session-log', compact('sessions'));
    }

    /** Career Library Report (Report #27) */
    public function careerLibrary(Request $request)
    {
        $careers = Career::with(['subjects', 'universityPrograms'])->get();

        $export = $this->maybeExport($request, 'career_library', $careers);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.career-library', compact('careers'));
    }

    /** University Program Mapping (Report #28) */
    public function universityMapping(Request $request)
    {
        $programs = UniversityProgram::with(['careers', 'subjects'])->get();

        $export = $this->maybeExport($request, 'university_mapping', $programs);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.university-mapping', compact('programs'));
    }

    /** Career Fair Recommendation (Report #29) */
    public function careerFairRecommendations(Request $request)
    {
        $recommendations = $this->getCareerFairRecommendations();

        $export = $this->maybeExport($request, 'career_fair', $recommendations);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.career-fair', compact('recommendations'));
    }

    /** Student Success Stories (Report #30) */
    public function successStories(Request $request)
    {
        $stories = $this->getSuccessStories();

        $export = $this->maybeExport($request, 'success_stories', $stories);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.success-stories', compact('stories'));
    }

    /** Assessment Engagement Report (Report #31) */
    public function assessmentEngagement(Request $request)
    {
        $data = $this->getAssessmentEngagementData();

        $export = $this->maybeExport($request, 'assessment_engagement', $data);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.assessment-engagement', compact('data'));
    }

    /** Career Path Tracking (Report #32) */
    public function careerPathTracking(Request $request)
    {
        $trackingData = $this->getCareerPathTrackingData();

        $export = $this->maybeExport($request, 'career_tracking', $trackingData);
        if ($export) {
            return $export;
        }

        return view('counsellor.reports.career-tracking', compact('trackingData'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Data Collection Methods
    // ──────────────────────────────────────────────────────────────────────────

    private function getReportData($reportType, $dateFrom, $dateTo, $formLevel, $stream, $category)
    {
        return match($reportType) {
            'career_analytics' => $this->getCareerAnalyticsData($dateFrom, $dateTo, $formLevel, $stream),
            'intervention_list' => ['title' => 'Student Intervention Priority List', 'students' => $this->getInterventionStudents()],
            'session_log' => ['title' => 'Counselling Session Log', 'sessions' => $this->getSessionLogData($dateFrom, $dateTo)],
            'career_library' => ['title' => 'Career Library Report', 'careers' => Career::with(['subjects', 'universityPrograms'])->get()],
            'university_mapping' => ['title' => 'University Program Mapping', 'programs' => UniversityProgram::with(['careers', 'subjects'])->get()],
            'career_fair' => ['title' => 'Career Fair Recommendations', 'recommendations' => $this->getCareerFairRecommendations()],
            'success_stories' => ['title' => 'Student Success Stories', 'stories' => $this->getSuccessStories()],
            'assessment_engagement' => $this->getAssessmentEngagementData(),
            'career_tracking' => $this->getCareerPathTrackingData(),
            default => $this->getCareerAnalyticsData($dateFrom, $dateTo, $formLevel, $stream),
        };
    }

    private function getCareerAnalyticsData($dateFrom, $dateTo, $formLevel, $stream)
    {
        $query = StudentProfile::with(['user.recommendations']);

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($formLevel) $query->where('form_level', $formLevel);
        if ($stream) $query->where('stream', $stream);

        $students = $query->get();

        // Career interests distribution
        $careerInterests = [];
        foreach ($students as $student) {
            $topCareer = $student->user->recommendations
                ->where('type', 'career')
                ->sortByDesc('confidence_score')
                ->first();
            if ($topCareer && $topCareer->recommended) {
                $category = $topCareer->recommended->category ?? 'Other';
                $careerInterests[$category] = ($careerInterests[$category] ?? 0) + 1;
            }
        }

        // Form level breakdown
        $formBreakdown = $students->groupBy('form_level')->map->count()->toArray();

        // Stream breakdown
        $streamBreakdown = $students->groupBy('stream')->map->count()->toArray();

        // Assessment completion rate
        $totalStudents = $students->count();
        $completedAssessments = $students->filter(function($s) {
            return AssessmentAttempt::where('student_id', $s->user_id)
                ->where('status', 'completed')
                ->exists();
        })->count();

        $completionRate = $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0;

        // Top career matches overall
        $topCareers = Recommendation::where('type', 'career')
            ->select('recommended_id', DB::raw('count(*) as count'), DB::raw('avg(confidence_score) as avg_score'))
            ->groupBy('recommended_id')
            ->with('recommended')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        return [
            'title' => 'School-Wide Career Analytics',
            'career_interests' => $careerInterests,
            'form_breakdown' => $formBreakdown,
            'stream_breakdown' => $streamBreakdown,
            'completion_rate' => $completionRate,
            'total_students' => $totalStudents,
            'completed_assessments' => $completedAssessments,
            'top_careers' => $topCareers,
            'date_range' => ['from' => $dateFrom, 'to' => $dateTo],
        ];
    }

    private function getInterventionStudents()
    {
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'recommendations'])
            ->get();

        $interventionList = [];

        foreach ($students as $student) {
            $hasAssessment = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();

            $bestMatch = $student->recommendations
                ->where('type', 'career')
                ->max('confidence_score') ?? 0;

            $needsIntervention = !$hasAssessment || $bestMatch < 60;

            if ($needsIntervention) {
                $interventionList[] = [
                    'id' => $student->id,
                    'name' => $student->name,
                    'form' => $student->studentProfile->form_level ?? 'N/A',
                    'stream' => $student->studentProfile->stream ?? 'N/A',
                    'has_assessment' => $hasAssessment,
                    'best_match_score' => round($bestMatch, 1),
                    'priority' => !$hasAssessment ? 'High' : ($bestMatch < 40 ? 'High' : 'Medium'),
                    'recommended_action' => !$hasAssessment ? 'Complete Career Assessment' : 'Career Counselling Session',
                ];
            }
        }

        // Sort by priority (High first, then by score ascending)
        usort($interventionList, function($a, $b) {
            if ($a['priority'] === $b['priority']) {
                return $a['best_match_score'] <=> $b['best_match_score'];
            }
            return $a['priority'] === 'High' ? -1 : 1;
        });

        return $interventionList;
    }

    private function getSessionLogData($dateFrom, $dateTo)
    {
        $query = ActivityLog::where('action', 'counselling_session')
            ->with('user');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        return $query->latest()->get();
    }

    private function getCareerFairRecommendations()
    {
        // Get top career categories based on student interest
        $recommendations = Recommendation::where('type', 'career')
            ->with('recommended')
            ->get()
            ->groupBy(function($rec) {
                return $rec->recommended->category ?? 'Other';
            })
            ->map(function($group) {
                return [
                    'count' => $group->count(),
                    'careers' => $group->take(5)->map(function($rec) {
                        return [
                            'title' => $rec->recommended->title ?? 'Unknown',
                            'avg_score' => round($rec->confidence_score, 1),
                        ];
                    })->values()
                ];
            })
            ->sortByDesc('count')
            ->take(6);

        return $recommendations;
    }

    private function getSuccessStories()
    {
        // Find students with high match scores who have saved recommendations
        $students = User::where('role', 'student')
            ->with(['studentProfile', 'recommendations'])
            ->get()
            ->filter(function($student) {
                $bestMatch = $student->recommendations
                    ->where('type', 'career')
                    ->max('confidence_score') ?? 0;
                $savedCount = $student->recommendations
                    ->where('status', 'accepted')
                    ->count();
                return $bestMatch >= 80 && $savedCount >= 2;
            })
            ->take(10)
            ->map(function($student) {
                $topCareer = $student->recommendations
                    ->where('type', 'career')
                    ->sortByDesc('confidence_score')
                    ->first();

                return [
                    'name' => $student->name,
                    'form' => $student->studentProfile->form_level ?? 'N/A',
                    'stream' => $student->studentProfile->stream ?? 'N/A',
                    'top_career' => $topCareer?->recommended?->title ?? 'N/A',
                    'match_score' => round($topCareer?->confidence_score ?? 0, 1),
                    'saved_recommendations' => $student->recommendations->where('status', 'accepted')->count(),
                ];
            });

        return $students;
    }

    private function getAssessmentEngagementData()
    {
        $students = User::where('role', 'student')->with('studentProfile')->get();

        $totalStudents = $students->count();
        $completedAssessments = AssessmentAttempt::where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');

        $byForm = [];
        $byStream = [];

        foreach ($students as $student) {
            $form = $student->studentProfile->form_level ?? 'Unknown';
            $stream = $student->studentProfile->stream ?? 'Unknown';
            $hasCompleted = AssessmentAttempt::where('student_id', $student->id)
                ->where('status', 'completed')
                ->exists();

            if (!isset($byForm[$form])) {
                $byForm[$form] = ['total' => 0, 'completed' => 0];
            }
            $byForm[$form]['total']++;
            if ($hasCompleted) $byForm[$form]['completed']++;

            if (!isset($byStream[$stream])) {
                $byStream[$stream] = ['total' => 0, 'completed' => 0];
            }
            $byStream[$stream]['total']++;
            if ($hasCompleted) $byStream[$stream]['completed']++;
        }

        foreach ($byForm as $form => &$data) {
            $data['rate'] = $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) : 0;
        }
        foreach ($byStream as $stream => &$data) {
            $data['rate'] = $data['total'] > 0 ? round(($data['completed'] / $data['total']) * 100, 1) : 0;
        }

        // Monthly trends
        $monthlyTrends = AssessmentAttempt::where('status', 'completed')
            ->select(DB::raw('DATE_FORMAT(completed_at, "%b %Y") as month'), DB::raw('count(*) as count'))
            ->groupBy('month')
            ->orderByRaw('MIN(completed_at)')
            ->limit(6)
            ->get();

        return [
            'total_students' => $totalStudents,
            'completed_assessments' => $completedAssessments,
            'completion_rate' => $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0,
            'by_form' => $byForm,
            'by_stream' => $byStream,
            'monthly_trends' => $monthlyTrends,
        ];
    }

    private function getCareerPathTrackingData()
    {
        // Get students' career interests over time
        $students = User::where('role', 'student')
            ->with(['recommendations.recommended', 'assessmentAttempts'])
            ->get();

        $tracking = [];

        foreach ($students as $student) {
            $careerHistory = [];
            foreach ($student->assessmentAttempts->where('status', 'completed') as $attempt) {
                $topCareer = $student->recommendations
                    ->where('type', 'career')
                    ->where('attempt_id', $attempt->id)
                    ->sortByDesc('confidence_score')
                    ->first();

                if ($topCareer && $topCareer->recommended) {
                    $careerHistory[] = [
                        'date' => $attempt->completed_at->format('Y-m-d'),
                        'career' => $topCareer->recommended->title,
                        'score' => round($topCareer->confidence_score, 1),
                    ];
                }
            }

            if (count($careerHistory) > 0) {
                $tracking[] = [
                    'student_name' => $student->name,
                    'history' => $careerHistory,
                    'latest_career' => end($careerHistory)['career'] ?? 'N/A',
                    'trend' => $this->calculateTrend($careerHistory),
                ];
            }
        }

        return $tracking;
    }

    private function calculateTrend($history)
    {
        if (count($history) < 2) return 'New';

        $firstScore = $history[0]['score'];
        $lastScore = end($history)['score'];

        if ($lastScore > $firstScore + 10) return 'Improving ↑';
        if ($lastScore < $firstScore - 10) return 'Declining ↓';
        return 'Stable →';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Export Methods
    // ──────────────────────────────────────────────────────────────────────────

    private function maybeExport(Request $request, string $reportType, mixed $data): mixed
    {
        $format = $request->input('format', 'html');
        if ($format === 'html' || $format === '') {
            return null;
        }

        return $this->exportReport(
            $reportType,
            $data,
            $format,
            $request->input('date_from'),
            $request->input('date_to')
        );
    }

    private function exportReport(string $reportType, mixed $data, string $format, ?string $dateFrom, ?string $dateTo): mixed
    {
        if ($format === 'csv') {
            return $this->streamCsv($this->buildCsvRows($reportType, $data), $reportType);
        }

        if ($format === 'pdf') {
            $viewSlug = str_replace('_', '-', $reportType);
            $view = 'counsellor.reports.pdf.' . $viewSlug;

            if (!view()->exists($view)) {
                return $this->streamCsv($this->buildCsvRows($reportType, $data), $reportType);
            }

            return $this->exportToPdf($view, $this->preparePdfViewData($reportType, $data), $reportType);
        }

        abort(400, 'Unsupported export format');
    }

    private function preparePdfViewData(string $reportType, mixed $data): array
    {
        return match ($reportType) {
            'career_analytics' => ['data' => $data],
            'intervention_list' => ['students' => is_array($data) ? $data : []],
            'session_log' => ['sessions' => $data],
            'career_library' => ['careers' => $data],
            'university_mapping' => ['programs' => $data],
            'career_fair' => ['recommendations' => $data],
            'success_stories' => ['stories' => $data],
            'assessment_engagement' => ['data' => $data],
            'career_tracking' => ['trackingData' => $data],
            default => is_array($data) ? $data : ['data' => $data],
        };
    }

    private function buildCsvRows(string $reportType, mixed $data): array
    {
        return match ($reportType) {
            'career_analytics' => $this->buildCareerAnalyticsCsv($data),
            'intervention_list' => $this->buildInterventionCsv($data),
            'session_log' => $this->buildSessionLogCsv($data),
            'career_library' => $this->buildCareerLibraryCsv($data),
            'university_mapping' => $this->buildUniversityMappingCsv($data),
            'career_fair' => $this->buildCareerFairCsv($data),
            'success_stories' => $this->buildSuccessStoriesCsv($data),
            'assessment_engagement' => $this->buildAssessmentEngagementCsv($data),
            'career_tracking' => $this->buildCareerTrackingCsv($data),
            default => [],
        };
    }

    private function buildCareerAnalyticsCsv(array $data): array
    {
        $rows = [];
        foreach ($data['career_interests'] ?? [] as $category => $count) {
            $rows[] = [
                'Category' => $category,
                'Student Count' => $count,
            ];
        }
        foreach ($data['top_careers'] ?? [] as $career) {
            $rows[] = [
                'Top Career' => $career->recommended->title ?? 'N/A',
                'Category' => $career->recommended->category ?? 'N/A',
                'Matches' => $career->count,
                'Avg Score' => round($career->avg_score, 1),
            ];
        }
        if (empty($rows)) {
            $rows[] = [
                'Total Students' => $data['total_students'] ?? 0,
                'Completed Assessments' => $data['completed_assessments'] ?? 0,
                'Completion Rate' => ($data['completion_rate'] ?? 0) . '%',
            ];
        }
        return $rows;
    }

    private function buildInterventionCsv(mixed $data): array
    {
        $students = is_array($data) ? $data : ($data['students'] ?? []);
        return array_map(fn($s) => [
            'Name' => $s['name'],
            'Form' => $s['form'],
            'Stream' => $s['stream'],
            'Has Assessment' => $s['has_assessment'] ? 'Yes' : 'No',
            'Best Match Score' => $s['best_match_score'],
            'Priority' => $s['priority'],
            'Recommended Action' => $s['recommended_action'],
        ], $students);
    }

    private function buildSessionLogCsv(mixed $sessions): array
    {
        return collect($sessions)->map(function ($session) {
            $notes = $session->meta['notes'] ?? $session->description ?? '';
            return [
                'Student' => $session->user->name ?? 'Unknown',
                'Date' => $session->created_at?->format('Y-m-d H:i'),
                'Notes' => $notes,
            ];
        })->all();
    }

    private function buildCareerLibraryCsv(mixed $careers): array
    {
        return collect($careers)->map(fn($career) => [
            'Title' => $career->title,
            'Category' => $career->category,
            'Description' => Str::limit($career->description ?? '', 120),
            'Subjects' => $career->subjects->pluck('name')->implode(', '),
            'Programs' => $career->universityPrograms->pluck('name')->implode(', '),
        ])->all();
    }

    private function buildUniversityMappingCsv(mixed $programs): array
    {
        return collect($programs)->map(fn($program) => [
            'Program' => $program->name,
            'University' => $program->university ?? 'N/A',
            'Duration' => $program->duration ?? 'N/A',
            'Careers' => $program->careers->pluck('title')->implode(', '),
            'Subjects' => $program->subjects->pluck('name')->implode(', '),
        ])->all();
    }

    private function buildCareerFairCsv(mixed $recommendations): array
    {
        $rows = [];
        foreach ($recommendations as $category => $group) {
            $rows[] = [
                'Category' => $category,
                'Interest Count' => $group['count'] ?? 0,
                'Top Careers' => collect($group['careers'] ?? [])->pluck('title')->implode(', '),
            ];
        }
        return $rows;
    }

    private function buildSuccessStoriesCsv(mixed $stories): array
    {
        return collect($stories)->map(fn($story) => [
            'Name' => $story['name'],
            'Form' => $story['form'],
            'Stream' => $story['stream'],
            'Top Career' => $story['top_career'],
            'Match Score' => $story['match_score'],
            'Saved Recommendations' => $story['saved_recommendations'],
        ])->all();
    }

    private function buildAssessmentEngagementCsv(array $data): array
    {
        $rows = [
            [
                'Metric' => 'Total Students',
                'Value' => $data['total_students'] ?? 0,
            ],
            [
                'Metric' => 'Completed Assessments',
                'Value' => $data['completed_assessments'] ?? 0,
            ],
            [
                'Metric' => 'Completion Rate',
                'Value' => ($data['completion_rate'] ?? 0) . '%',
            ],
        ];

        foreach ($data['by_form'] ?? [] as $form => $stats) {
            $rows[] = [
                'Metric' => 'Form: ' . $form,
                'Value' => $stats['completed'] . '/' . $stats['total'] . ' (' . $stats['rate'] . '%)',
            ];
        }

        return $rows;
    }

    private function buildCareerTrackingCsv(mixed $trackingData): array
    {
        return collect($trackingData)->map(fn($row) => [
            'Student' => $row['student_name'],
            'Latest Career' => $row['latest_career'],
            'Trend' => $row['trend'],
            'History' => collect($row['history'])->map(fn($h) => $h['date'] . ': ' . $h['career'] . ' (' . $h['score'] . '%)')->implode('; '),
        ])->all();
    }

    private function streamCsv(array $rows, string $baseName): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $baseName . '_' . now()->format('Y-m-d');

        if (empty($rows)) {
            $rows = [['Message' => 'No data available for this report']];
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ];

        $callback = function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($handle, array_keys($rows[0]));
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function exportToPdf(string $view, array $data, string $baseName): mixed
    {
        $data['generatedBy'] = auth()->user()->name;
        $data['generatedAt'] = now()->format('d M Y, H:i');

        $pdf = PDF::loadView($view, $data)
            ->setPaper('a4', 'portrait');

        return $pdf->download($baseName . '_' . now()->format('Y-m-d') . '.pdf');
    }

    private function getFilterOptions()
    {
        return [
            'form_levels' => ['Form 1', 'Form 2', 'Form 3', 'Form 4'],
            'streams' => ['Sciences', 'Humanities', 'Languages', 'Commerce', 'General'],
            'career_categories' => Career::distinct()->pluck('category')->toArray(),
            'report_types' => [
                ['value' => 'career_analytics', 'label' => '📊 School-Wide Career Analytics', 'description' => 'Comprehensive career interest analysis with demographics'],
                ['value' => 'intervention_list', 'label' => '⚠️ Student Intervention Priority List', 'description' => 'Students needing immediate career guidance'],
                ['value' => 'session_log', 'label' => '📝 Counselling Session Log', 'description' => 'Complete history of counselling sessions'],
                ['value' => 'career_library', 'label' => '📚 Career Library Report', 'description' => 'Complete catalog of careers with requirements'],
                ['value' => 'university_mapping', 'label' => '🏛️ University Program Mapping', 'description' => 'University programs with entry requirements'],
                ['value' => 'career_fair', 'label' => '🎪 Career Fair Recommendations', 'description' => 'Suggested career clusters for events'],
                ['value' => 'success_stories', 'label' => '⭐ Student Success Stories', 'description' => 'Students who successfully followed recommendations'],
                ['value' => 'assessment_engagement', 'label' => '📈 Assessment Engagement Report', 'description' => 'Assessment completion rates by demographics'],
                ['value' => 'career_tracking', 'label' => '🔄 Career Path Tracking', 'description' => 'Longitudinal career preference tracking'],
            ],
        ];
    }
}
