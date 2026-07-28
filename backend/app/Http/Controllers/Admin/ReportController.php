<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, AssessmentAttempt, Career, Recommendation, StudentProfile, Subject, UniversityProgram, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Show the reports dashboard with dropdown and filters
     */
    public function index(Request $request)
    {
        $reportType = $request->input('report_type');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $format = $request->input('format', 'html');
        $role = $request->input('role');
        $formLevel = $request->input('form_level');
        $stream = $request->input('stream');
        $category = $request->input('category');
        $status = $request->input('status');

        // Build query based on report type (skip if none selected)
        $data = $reportType
            ? $this->getReportData($reportType, $dateFrom, $dateTo, $role, $formLevel, $stream, $category, $status)
            : [];

        // If export requested (not HTML), return file download
        if ($format !== 'html' && $reportType) {
            return $this->exportReport($reportType, $data, $format, $dateFrom, $dateTo);
        }

        // Get filter options for dropdowns
        $filterOptions = $this->getFilterOptions();

        return view('admin.reports.index', compact(
            'reportType', 'data', 'dateFrom', 'dateTo', 'role', 'formLevel',
            'stream', 'category', 'status', 'filterOptions'
        ));
    }

    /**
     * Export report (delegates to index with format parameter)
     */
    public function export(Request $request)
    {
        if (!$request->filled('report_type')) {
            $request->merge(['report_type' => 'users']);
        }
        if ($request->input('format', 'html') === 'html') {
            $request->merge(['format' => 'csv']);
        }

        return $this->index($request);
    }

    /**
     * Export report to PDF or CSV
     */
    private function exportReport($reportType, $data, $format, $dateFrom, $dateTo): Response|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $reportType . '_' . now()->format('Y-m-d_His');

        if ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($data) {
                $handle = fopen('php://output', 'w');
                // Add UTF-8 BOM for Excel compatibility
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $data['headers']);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, (array) $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        // PDF Export - Check if view exists, if not return CSV fallback
        if (!view()->exists('admin.reports.pdf')) {
            // Fallback to CSV if PDF view doesn't exist
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            ];

            $callback = function() use ($data) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                fputcsv($handle, $data['headers']);
                foreach ($data['rows'] as $row) {
                    fputcsv($handle, (array) $row);
                }
                fclose($handle);
            };

            return response()->stream($callback, 200, $headers);
        }

        $pdf = PDF::loadView('admin.reports.pdf', [
            'data' => $data,
            'reportType' => $reportType,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'generatedBy' => auth()->user()->name,
            'generatedAt' => now()->format('Y-m-d H:i:s')
        ]);

        $pdf->setPaper('a4', 'landscape');
        $pdf->setOptions([
            'defaultFont' => 'DejaVu Sans',
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true
        ]);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Get report data based on type and filters
     */
    private function getReportData($reportType, $dateFrom, $dateTo, $role, $formLevel, $stream, $category, $status)
    {
        return match($reportType) {
            'user_analytics' => $this->getUserAnalytics($dateFrom, $dateTo, $role, $status),
            'student_analytics' => $this->getStudentAnalytics($dateFrom, $dateTo, $formLevel, $stream),
            'assessment_analytics' => $this->getAssessmentAnalytics($dateFrom, $dateTo, $formLevel, $stream),
            'career_analytics' => $this->getCareerAnalytics($dateFrom, $dateTo, $category),
            'recommendation_analytics' => $this->getRecommendationAnalytics($dateFrom, $dateTo, $formLevel, $stream),
            'university_analytics' => $this->getUniversityAnalytics($dateFrom, $dateTo, $formLevel, $stream),
            'academic_performance' => $this->getAcademicPerformance($dateFrom, $dateTo, $formLevel, $stream),
            'activity_logs' => $this->getActivityLogs($dateFrom, $dateTo, $role),
            'engagement_summary' => $this->getEngagementSummary($dateFrom, $dateTo),
            default => $this->getUserAnalytics($dateFrom, $dateTo, $role, $status),
        };
    }

    /**
     * 1. User Analytics Report
     */
    private function getUserAnalytics($dateFrom, $dateTo, $role, $status)
    {
        $query = User::query();

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($role) $query->where('role', $role);
        if ($status === 'active') $query->where('is_active', true);
        if ($status === 'inactive') $query->where('is_active', false);

        $users = $query->withCount('activityLogs')->get();
        $latestLogs = ActivityLog::whereIn('user_id', $users->pluck('id'))
            ->selectRaw('user_id, MAX(created_at) as last_at')
            ->groupBy('user_id')
            ->get()
            ->keyBy('user_id');

        return [
            'title' => 'User Analytics Report',
            'headers' => ['#', 'Name', 'Email', 'Role', 'Status', 'Registered Date', 'Last Activity', 'Actions Count'],
            'rows' => $users->map(function($user, $index) use ($latestLogs) {
                $lastAt = $latestLogs->get($user->id)?->last_at;
                return [
                    $index + 1,
                    $user->name,
                    $user->email,
                    ucfirst($user->role),
                    $user->is_active ? 'Active' : 'Inactive',
                    $user->created_at->format('Y-m-d'),
                    $lastAt ? \Carbon\Carbon::parse($lastAt)->format('Y-m-d H:i') : 'Never',
                    $user->activity_logs_count,
                ];
            }),
            'summary' => [
                'total_users' => $users->count(),
                'active_users' => $users->where('is_active', true)->count(),
                'inactive_users' => $users->where('is_active', false)->count(),
                'students' => $users->where('role', 'student')->count(),
                'teachers' => $users->where('role', 'teacher')->count(),
                'counsellors' => $users->where('role', 'counsellor')->count(),
                'parents' => $users->where('role', 'parent')->count(),
                'admins' => $users->where('role', 'admin')->count(),
            ],
        ];
    }

    /**
     * 2. Student Analytics Report
     */
    private function getStudentAnalytics($dateFrom, $dateTo, $formLevel, $stream)
    {
        $query = StudentProfile::with('user');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($formLevel) $query->where('form_level', $formLevel);
        if ($stream) $query->where('stream', $stream);

        $students = $query->get();

        return [
            'title' => 'Student Analytics Report',
            'headers' => ['#', 'Student Name', 'Student Number', 'Form', 'Stream', 'Email', 'Assessment Status', 'Top Career', 'Match Score'],
            'rows' => $students->map(function($profile, $index) {
                $user = $profile->user;
                $hasAssessment = AssessmentAttempt::where('student_id', $user->id)->where('status', 'completed')->exists();
                $topCareer = Recommendation::where('student_id', $user->id)
                    ->where('type', 'career')
                    ->orderByDesc('confidence_score')
                    ->first();

                return [
                    $index + 1,
                    $user->name,
                    $profile->student_number ?? 'N/A',
                    $profile->form_level ?? 'N/A',
                    $profile->stream ?? 'N/A',
                    $user->email,
                    $hasAssessment ? 'Completed' : 'Pending',
                    $topCareer?->recommended?->title ?? 'N/A',
                    $topCareer?->confidence_score ? round($topCareer->confidence_score) . '%' : 'N/A',
                ];
            }),
            'summary' => [
                'total_students' => $students->count(),
                'by_form' => $students->groupBy('form_level')->map->count()->toArray(),
                'by_stream' => $students->groupBy('stream')->map->count()->toArray(),
                'assessments_completed' => $students->filter(function($p) {
                    return AssessmentAttempt::where('student_id', $p->user_id)->where('status', 'completed')->exists();
                })->count(),
            ],
        ];
    }

    /**
     * 3. Assessment Analytics Report
     */
    private function getAssessmentAnalytics($dateFrom, $dateTo, $formLevel, $stream)
    {
        $query = AssessmentAttempt::with('student.studentProfile')
            ->withCount(['responses', 'recommendations'])
            ->where('status', 'completed');

        if ($dateFrom) $query->whereDate('completed_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('completed_at', '<=', $dateTo);
        if ($formLevel) $query->whereHas('student.studentProfile', fn($q) => $q->where('form_level', $formLevel));
        if ($stream) $query->whereHas('student.studentProfile', fn($q) => $q->where('stream', $stream));

        $assessments = $query->get();

        return [
            'title' => 'Assessment Analytics Report',
            'headers' => ['#', 'Student', 'Form', 'Stream', 'Completed Date', 'Questions Answered', 'Career Categories', 'Recommendations'],
            'rows' => $assessments->map(function($attempt, $index) {
                $student = $attempt->student;
                $profile = $student->studentProfile;
                $categories = $attempt->tallyCareerCategories();

                return [
                    $index + 1,
                    $student->name,
                    $profile?->form_level ?? 'N/A',
                    $profile?->stream ?? 'N/A',
                    $attempt->completed_at?->format('Y-m-d H:i') ?? 'N/A',
                    $attempt->responses_count,
                    implode(', ', array_slice(array_keys($categories), 0, 3)),
                    $attempt->recommendations_count,
                ];
            }),
            'summary' => [
                'total_assessments' => $assessments->count(),
                'avg_questions' => round($assessments->avg('responses_count'), 1),
                'avg_recommendations' => round($assessments->avg('recommendations_count'), 1),
            ],
        ];
    }

    /**
     * 4. Career Analytics Report
     */
    private function getCareerAnalytics($dateFrom, $dateTo, $category)
    {
        $query = Career::withCount('recommendations');

        if ($category) $query->where('category', $category);

        $careers = $query->get();

        return [
            'title' => 'Career Analytics Report',
            'headers' => ['#', 'Career Title', 'Category', 'Description', 'Required Skills', 'Recommendations', 'Avg Match Score'],
            'rows' => $careers->map(function($career, $index) {
                $avgScore = Recommendation::where('type', 'career')
                    ->where('recommended_id', $career->id)
                    ->where('recommended_type', Career::class)
                    ->avg('confidence_score');

                return [
                    $index + 1,
                    $career->title,
                    $career->category,
                    substr($career->description, 0, 80) . '...',
                    substr($career->required_skills, 0, 60) . '...',
                    $career->recommendations_count,
                    $avgScore ? round($avgScore, 1) . '%' : 'N/A',
                ];
            }),
            'summary' => [
                'total_careers' => $careers->count(),
                'by_category' => $careers->groupBy('category')->map->count()->toArray(),
                'total_recommendations' => $careers->sum('recommendations_count'),
            ],
        ];
    }

    /**
     * 5. Recommendation Analytics Report
     */
    private function getRecommendationAnalytics($dateFrom, $dateTo, $formLevel, $stream)
    {
        $query = Recommendation::with(['student.studentProfile', 'recommended']);

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($formLevel) $query->whereHas('student.studentProfile', fn($q) => $q->where('form_level', $formLevel));
        if ($stream) $query->whereHas('student.studentProfile', fn($q) => $q->where('stream', $stream));

        $recommendations = $query->latest()->limit(1000)->get();

        return [
            'title' => 'Recommendation Analytics Report',
            'headers' => ['#', 'Student', 'Form', 'Stream', 'Type', 'Recommended Item', 'Confidence', 'Status', 'Date'],
            'rows' => $recommendations->map(function($rec, $index) {
                $student = $rec->student;
                $profile = $student->studentProfile;

                return [
                    $index + 1,
                    $student->name,
                    $profile?->form_level ?? 'N/A',
                    $profile?->stream ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $rec->type)),
                    $rec->recommended?->title ?? $rec->recommended?->name ?? 'N/A',
                    round($rec->confidence_score) . '%',
                    ucfirst($rec->status),
                    $rec->created_at->format('Y-m-d'),
                ];
            }),
            'summary' => [
                'total_recommendations' => $recommendations->count(),
                'by_type' => $recommendations->groupBy('type')->map->count()->toArray(),
                'by_status' => $recommendations->groupBy('status')->map->count()->toArray(),
                'avg_confidence' => round($recommendations->avg('confidence_score'), 1) . '%',
            ],
        ];
    }

    /**
     * 6. University Analytics Report
     */
    private function getUniversityAnalytics($dateFrom, $dateTo, $formLevel, $stream)
    {
        $programs = UniversityProgram::withCount('recommendations')->get();

        return [
            'title' => 'University Program Analytics Report',
            'headers' => ['#', 'Program Name', 'University', 'Faculty', 'Min Points', 'Requirements', 'Recommendations'],
            'rows' => $programs->map(function($program, $index) {
                return [
                    $index + 1,
                    $program->name,
                    $program->university,
                    $program->faculty ?? 'N/A',
                    $program->minimum_points ?? 'N/A',
                    substr($program->entry_requirements ?? 'N/A', 0, 60) . '...',
                    $program->recommendations_count,
                ];
            }),
            'summary' => [
                'total_programs' => $programs->count(),
                'by_university' => $programs->groupBy('university')->map->count()->toArray(),
                'total_recommendations' => $programs->sum('recommendations_count'),
            ],
        ];
    }

    /**
     * 7. Academic Performance Report
     */
    private function getAcademicPerformance($dateFrom, $dateTo, $formLevel, $stream)
    {
        $query = \App\Models\AcademicResult::with(['student.studentProfile', 'subject']);

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($formLevel) $query->whereHas('student.studentProfile', fn($q) => $q->where('form_level', $formLevel));
        if ($stream) $query->whereHas('student.studentProfile', fn($q) => $q->where('stream', $stream));

        $results = $query->latest()->limit(1000)->get();

        return [
            'title' => 'Academic Performance Report',
            'headers' => ['#', 'Student', 'Form', 'Stream', 'Subject', 'Score', 'Grade', 'Term', 'Remarks'],
            'rows' => $results->map(function($result, $index) {
                $student = $result->student;
                $profile = $student->studentProfile;

                return [
                    $index + 1,
                    $student->name,
                    $profile?->form_level ?? 'N/A',
                    $profile?->stream ?? 'N/A',
                    $result->subject?->name ?? 'N/A',
                    $result->score . '%',
                    $result->grade,
                    $result->term,
                    substr($result->teacher_remarks ?? 'N/A', 0, 50),
                ];
            }),
            'summary' => [
                'total_results' => $results->count(),
                'students_count' => $results->groupBy('student_id')->count(),
                'avg_score' => round($results->avg('score'), 1) . '%',
            ],
        ];
    }

    /**
     * 8. Activity Logs Report
     */
    private function getActivityLogs($dateFrom, $dateTo, $role)
    {
        $query = ActivityLog::with('user');

        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);
        if ($role) $query->whereHas('user', fn($q) => $q->where('role', $role));

        $logs = $query->latest()->limit(1000)->get();

        return [
            'title' => 'System Activity Logs Report',
            'headers' => ['#', 'User', 'Role', 'Action', 'IP Address', 'Timestamp', 'Metadata'],
            'rows' => $logs->map(function($log, $index) {
                return [
                    $index + 1,
                    $log->user?->name ?? 'System',
                    $log->user?->role ?? 'System',
                    str_replace('_', ' ', ucfirst($log->action)),
                    $log->ip_address ?? 'N/A',
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->meta ? json_encode($log->meta) : 'N/A',
                ];
            }),
            'summary' => [
                'total_logs' => $logs->count(),
                'unique_users' => $logs->groupBy('user_id')->count(),
                'by_action' => $logs->groupBy('action')->map->count()->toArray(),
            ],
        ];
    }

    /**
     * 9. Engagement Summary Report
     */
    private function getEngagementSummary($dateFrom, $dateTo)
    {
        $query = User::query();
        if ($dateFrom) $query->whereDate('created_at', '>=', $dateFrom);
        if ($dateTo) $query->whereDate('created_at', '<=', $dateTo);

        $totalUsers = $query->count();
        $totalAssessments = AssessmentAttempt::where('status', 'completed')->count();
        $totalRecommendations = Recommendation::count();
        $totalReportDownloads = ActivityLog::where('action', 'report_downloaded')->count();

        return [
            'title' => 'System Engagement Summary Report',
            'headers' => ['Metric', 'Value', 'Notes'],
            'rows' => collect([
                ['Total Users', number_format($totalUsers), 'All registered users'],
                ['Completed Assessments', number_format($totalAssessments), 'Students who finished assessment'],
                ['Generated Recommendations', number_format($totalRecommendations), 'Total career & subject matches'],
                ['Report Downloads', number_format($totalReportDownloads), 'Exported reports'],
            ]),
            'summary' => [
                'total_users' => $totalUsers,
                'total_assessments' => $totalAssessments,
                'total_recommendations' => $totalRecommendations,
                'total_report_downloads' => $totalReportDownloads,
            ],
        ];
    }

    /**
     * Get filter options for dropdowns
     */
    private function getFilterOptions()
    {
        return [
            'roles' => ['student', 'teacher', 'counsellor', 'parent', 'admin'],
            'form_levels' => ['Form 1', 'Form 2', 'Form 3', 'Form 4'],
            'streams' => ['Sciences', 'Humanities', 'Languages', 'Commerce', 'General'],
            'career_categories' => Career::distinct()->pluck('category')->toArray(),
            'statuses' => ['active', 'inactive'],
            'report_types' => [
                ['value' => 'user_analytics', 'label' => '📊 User Analytics', 'description' => 'Overview of all users by role and status'],
                ['value' => 'student_analytics', 'label' => '🎓 Student Analytics', 'description' => 'Detailed student demographics and assessment status'],
                ['value' => 'assessment_analytics', 'label' => '📝 Assessment Analytics', 'description' => 'Assessment completion rates and trends'],
                ['value' => 'career_analytics', 'label' => '🎯 Career Analytics', 'description' => 'Career popularity and match statistics'],
                ['value' => 'recommendation_analytics', 'label' => '💡 Recommendation Analytics', 'description' => 'Recommendation generation and acceptance rates'],
                ['value' => 'university_analytics', 'label' => '🏛️ University Analytics', 'description' => 'University program popularity'],
                ['value' => 'academic_performance', 'label' => '📚 Academic Performance', 'description' => 'Student academic results'],
                ['value' => 'activity_logs', 'label' => '📋 Activity Logs', 'description' => 'System activity and user actions'],
                ['value' => 'engagement_summary', 'label' => '📈 Engagement Summary', 'description' => 'Key metrics overview'],
            ],
        ];
    }
}
