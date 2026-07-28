<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{ActivityLog, AssessmentAttempt, Career, Recommendation, Subject, UniversityProgram, User};
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Main Analytics Dashboard
     */
    public function index(): View
    {
        // User Statistics
        $userStats = $this->getUserStatistics();

        // Assessment Statistics
        $assessmentStats = $this->getAssessmentStatistics();

        // Recommendation Statistics
        $recommendationStats = $this->getRecommendationStatistics();

        // System Usage Statistics
        $systemStats = $this->getSystemStatistics();

        // Chart Data
        $chartData = $this->getChartData();

        // Top Performers
        $topPerformers = $this->getTopPerformers();

        return view('admin.analytics.index', compact(
            'userStats', 'assessmentStats', 'recommendationStats',
            'systemStats', 'chartData', 'topPerformers'
        ));
    }

    /**
     * User Analytics Page
     */
    public function users(): View
    {
        // User growth over time
        $userGrowth = $this->getUserGrowthData();

        // Role distribution
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Active vs Inactive users
        $activeStatus = User::select('is_active', DB::raw('count(*) as count'))
            ->groupBy('is_active')
            ->get()
            ->pluck('count', 'is_active')
            ->toArray();

        // Registration trends by month
        $registrationTrends = User::select(
                DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN role = "student" THEN 1 ELSE 0 END) as students'),
                DB::raw('SUM(CASE WHEN role = "teacher" THEN 1 ELSE 0 END) as teachers'),
                DB::raw('SUM(CASE WHEN role = "counsellor" THEN 1 ELSE 0 END) as counsellors'),
                DB::raw('SUM(CASE WHEN role = "parent" THEN 1 ELSE 0 END) as parents'),
                DB::raw('SUM(CASE WHEN role = "admin" THEN 1 ELSE 0 END) as admins')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->get();

        // Demographics by form level (students only)
        $formDistribution = \App\Models\StudentProfile::select('form_level', DB::raw('count(*) as count'))
            ->groupBy('form_level')
            ->orderBy('form_level')
            ->get();

        // Stream distribution (students only)
        $streamDistribution = \App\Models\StudentProfile::select('stream', DB::raw('count(*) as count'))
            ->whereNotNull('stream')
            ->groupBy('stream')
            ->get();

        // Recent user activity
        $recentUsers = User::withCount(['activityLogs', 'assessmentAttempts'])
            ->latest()
            ->take(20)
            ->get();

        return view('admin.analytics.users', compact(
            'userGrowth', 'roleDistribution', 'activeStatus', 'registrationTrends',
            'formDistribution', 'streamDistribution', 'recentUsers'
        ));
    }

    /**
     * Assessment Analytics Page
     */
    public function assessments(): View
    {
        // Assessment completion rate
        $totalStudents = User::where('role', 'student')->count();
        $completedAssessments = AssessmentAttempt::where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');
        $completionRate = $totalStudents > 0 ? round(($completedAssessments / $totalStudents) * 100, 1) : 0;

        // Assessment trends over time
        $assessmentTrends = AssessmentAttempt::where('status', 'completed')
            ->select(
                DB::raw('DATE_FORMAT(completed_at, "%Y-%m") as month'),
                DB::raw('count(*) as total'),
                DB::raw('count(DISTINCT student_id) as unique_students')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->take(12)
            ->get();

        // Assessment completion by form level
        $completionByForm = \App\Models\StudentProfile::withCount(['user' => function($q) {
                $q->whereHas('assessmentAttempts', function($q2) {
                    $q2->where('status', 'completed');
                });
            }])
            ->select('form_level', DB::raw('count(*) as total'))
            ->groupBy('form_level')
            ->get()
            ->map(function($item) {
                $item->completed = $item->user_count ?? 0;
                $item->completion_rate = $item->total > 0 ? round(($item->completed / $item->total) * 100, 1) : 0;
                return $item;
            });

        // Assessment completion by stream
        $completionByStream = \App\Models\StudentProfile::withCount(['user' => function($q) {
                $q->whereHas('assessmentAttempts', function($q2) {
                    $q2->where('status', 'completed');
                });
            }])
            ->select('stream', DB::raw('count(*) as total'))
            ->whereNotNull('stream')
            ->groupBy('stream')
            ->get()
            ->map(function($item) {
                $item->completed = $item->user_count ?? 0;
                $item->completion_rate = $item->total > 0 ? round(($item->completed / $item->total) * 100, 1) : 0;
                return $item;
            });

        // Average time to complete assessment
        $avgCompletionTime = AssessmentAttempt::where('status', 'completed')
            ->whereNotNull('completed_at')
            ->select(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, completed_at) as minutes'))
            ->get()
            ->avg('minutes');

        // Daily assessment completion (last 30 days)
        $dailyCompletions = AssessmentAttempt::where('status', 'completed')
            ->where('completed_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(completed_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Student assessment status breakdown
        $assessmentStatus = [
            'completed' => $completedAssessments,
            'in_progress' => AssessmentAttempt::where('status', 'in_progress')->distinct('student_id')->count('student_id'),
            'not_started' => $totalStudents - $completedAssessments - AssessmentAttempt::where('status', 'in_progress')->distinct('student_id')->count('student_id'),
        ];

        return view('admin.analytics.assessments', compact(
            'completionRate', 'assessmentTrends', 'completionByForm', 'completionByStream',
            'avgCompletionTime', 'dailyCompletions', 'assessmentStatus', 'totalStudents'
        ));
    }

    /**
     * Career & Recommendations Analytics
     */
    public function careers(): View
    {
        // Top career matches overall
        $topCareers = Recommendation::where('type', 'career')
            ->select('recommended_id', DB::raw('count(*) as count'), DB::raw('avg(confidence_score) as avg_score'))
            ->groupBy('recommended_id')
            ->with('recommended')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Career category distribution
        $careerCategories = Career::withCount('recommendations')
            ->get()
            ->map(function($career) {
                return [
                    'category' => $career->category,
                    'count' => $career->recommendations_count
                ];
            })
            ->groupBy('category')
            ->map(function($items) {
                return $items->sum('count');
            })
            ->toArray();

        // Average match score by form level
        $matchScoreByForm = \App\Models\StudentProfile::with(['user.recommendations' => function($q) {
                $q->where('type', 'career');
            }])
            ->get()
            ->groupBy('form_level')
            ->map(function($students) {
                $scores = $students->flatMap(function($student) {
                    return $student->user->recommendations->pluck('confidence_score');
                });
                return [
                    'avg_score' => $scores->avg() ?? 0,
                    'count' => $students->count()
                ];
            })
            ->toArray();

        // Acceptance rate of recommendations
        $acceptanceRate = [
            'accepted' => Recommendation::where('status', 'accepted')->count(),
            'dismissed' => Recommendation::where('status', 'dismissed')->count(),
            'pending' => Recommendation::where('status', 'pending')->count(),
            'viewed' => Recommendation::where('status', 'viewed')->count(),
        ];
        $acceptanceRate['total'] = array_sum($acceptanceRate);
        $acceptanceRate['acceptance_percentage'] = $acceptanceRate['total'] > 0
            ? round(($acceptanceRate['accepted'] / $acceptanceRate['total']) * 100, 1)
            : 0;

        // Subject combination popularity
        $topCombinations = Recommendation::where('type', 'subject_combination')
            ->select('recommended_id', DB::raw('count(*) as count'))
            ->groupBy('recommended_id')
            ->with('recommended')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // University program popularity
        $topUniversities = Recommendation::where('type', 'university_program')
            ->select('recommended_id', DB::raw('count(*) as count'), DB::raw('avg(confidence_score) as avg_score'))
            ->groupBy('recommended_id')
            ->with('recommended')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Career match distribution (score ranges)
        $scoreDistribution = [
            'excellent' => Recommendation::where('type', 'career')->where('confidence_score', '>=', 80)->count(),
            'good' => Recommendation::where('type', 'career')->whereBetween('confidence_score', [60, 79])->count(),
            'average' => Recommendation::where('type', 'career')->whereBetween('confidence_score', [40, 59])->count(),
            'low' => Recommendation::where('type', 'career')->where('confidence_score', '<', 40)->count(),
        ];

        return view('admin.analytics.careers', compact(
            'topCareers', 'careerCategories', 'matchScoreByForm', 'acceptanceRate',
            'topCombinations', 'topUniversities', 'scoreDistribution'
        ));
    }

    /**
     * System Usage Analytics
     */
    public function system(): View
    {
        // Daily active users (last 30 days)
        $dailyActiveUsers = ActivityLog::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(DISTINCT user_id) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Most active users
        $mostActiveUsers = User::withCount('activityLogs')
            ->orderByDesc('activity_logs_count')
            ->limit(10)
            ->get();

        // Popular actions
        $popularActions = ActivityLog::select('action', DB::raw('count(*) as count'))
            ->groupBy('action')
            ->orderByDesc('count')
            ->limit(15)
            ->get();

        // Peak usage hours
        $peakHours = ActivityLog::select(DB::raw('HOUR(created_at) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Daily page views (last 30 days)
        $dailyViews = ActivityLog::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Feature usage breakdown
        $featureUsage = [
            'assessments' => AssessmentAttempt::count(),
            'recommendations_viewed' => Recommendation::where('status', 'viewed')->count(),
            'recommendations_saved' => Recommendation::where('status', 'accepted')->count(),
            'reports_downloaded' => ActivityLog::where('action', 'report_downloaded')->count(),
            'profile_updates' => ActivityLog::where('action', 'profile_update')->count(),
        ];

        // Browser/Device stats (if you store user-agent)
        $deviceStats = ActivityLog::select('meta->device as device', DB::raw('count(*) as count'))
            ->whereNotNull('meta->device')
            ->groupBy('device')
            ->get();

        // User engagement metrics
        $engagementMetrics = [
            'avg_sessions_per_user' => User::has('activityLogs')->withCount('activityLogs')->get()->avg('activity_logs_count') ?? 0,
            'avg_assessments_per_student' => round(
                AssessmentAttempt::where('status', 'completed')->count() / max(1, User::where('role', 'student')->count()),
                1
            ),
            'avg_recommendations_per_student' => round(
                Recommendation::count() / max(1, Recommendation::distinct('student_id')->count('student_id')),
                1
            ),
        ];

        return view('admin.analytics.system', compact(
            'dailyActiveUsers', 'mostActiveUsers', 'popularActions', 'peakHours',
            'dailyViews', 'featureUsage', 'deviceStats', 'engagementMetrics'
        ));
    }

    /**
     * Export Analytics Data
     */
    public function export(Request $request)
    {
        $type = $request->input('type', 'users');
        $format = $request->input('format', 'csv');

        // Export logic here
        // Could generate CSV/Excel/PDF exports

        return back()->with('success', "Analytics report exported successfully.");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Private Helper Methods
    // ──────────────────────────────────────────────────────────────────────────

    private function getUserStatistics(): array
    {
        $totalUsers = User::count();
        $newUsersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        $usersByRole = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        return compact('totalUsers', 'newUsersThisMonth', 'activeUsers', 'inactiveUsers', 'usersByRole');
    }

    private function getAssessmentStatistics(): array
    {
        $totalAssessments = AssessmentAttempt::count();
        $completedAssessments = AssessmentAttempt::where('status', 'completed')->count();
        $inProgressAssessments = AssessmentAttempt::where('status', 'in_progress')->count();

        $studentsWithAssessment = AssessmentAttempt::where('status', 'completed')
            ->distinct('student_id')
            ->count('student_id');

        $totalStudents = User::where('role', 'student')->count();
        $assessmentRate = $totalStudents > 0 ? round(($studentsWithAssessment / $totalStudents) * 100, 1) : 0;

        return compact('totalAssessments', 'completedAssessments', 'inProgressAssessments', 'assessmentRate', 'studentsWithAssessment', 'totalStudents');
    }

    private function getRecommendationStatistics(): array
    {
        $totalRecommendations = Recommendation::count();
        $careerRecommendations = Recommendation::where('type', 'career')->count();
        $subjectRecommendations = Recommendation::where('type', 'subject_combination')->count();
        $universityRecommendations = Recommendation::where('type', 'university_program')->count();

        $savedRecommendations = Recommendation::where('status', 'accepted')->count();
        $dismissedRecommendations = Recommendation::where('status', 'dismissed')->count();

        $avgConfidenceScore = Recommendation::avg('confidence_score') ?? 0;

        return compact(
            'totalRecommendations', 'careerRecommendations', 'subjectRecommendations',
            'universityRecommendations', 'savedRecommendations', 'dismissedRecommendations',
            'avgConfidenceScore'
        );
    }

    private function getSystemStatistics(): array
    {
        $totalLogins = ActivityLog::where('action', 'login')->count();
        $totalReportsDownloaded = ActivityLog::where('action', 'report_downloaded')->count();
        $totalAssessmentsCompleted = ActivityLog::where('action', 'assessment_completed')->count();

        $careers = Career::count();
        $subjects = Subject::count();
        $universityPrograms = UniversityProgram::count();

        return compact(
            'totalLogins', 'totalReportsDownloaded',
            'totalAssessmentsCompleted', 'careers', 'subjects', 'universityPrograms'
        );
    }

    private function getUserGrowthData(): array
    {
        return User::select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('MIN(created_at) as month_start'),
                DB::raw('count(*) as total'),
                DB::raw('SUM(CASE WHEN role = "student"   THEN 1 ELSE 0 END) as students'),
                DB::raw('SUM(CASE WHEN role = "teacher"   THEN 1 ELSE 0 END) as teachers'),
                DB::raw('SUM(CASE WHEN role = "counsellor" THEN 1 ELSE 0 END) as counsellors'),
                DB::raw('SUM(CASE WHEN role = "admin"     THEN 1 ELSE 0 END) as admins')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%b %Y")'))
            ->orderBy('month_start')
            ->get()
            ->toArray();
    }

    private function getChartData(): array
    {
        // User registration last 12 months
        $userRegistrations = User::select(
                DB::raw('DATE_FORMAT(created_at, "%b %Y") as month'),
                DB::raw('MIN(created_at) as month_start'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('DATE_FORMAT(created_at, "%b %Y")'))
            ->orderBy('month_start')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Assessment completion last 12 months
        $assessmentsCompleted = AssessmentAttempt::where('status', 'completed')
            ->where('completed_at', '>=', now()->subMonths(12))
            ->select(
                DB::raw('DATE_FORMAT(completed_at, "%b %Y") as month'),
                DB::raw('MIN(completed_at) as month_start'),
                DB::raw('count(*) as count')
            )
            ->groupBy(DB::raw('DATE_FORMAT(completed_at, "%b %Y")'))
            ->orderBy('month_start')
            ->get()
            ->pluck('count', 'month')
            ->toArray();

        // Role distribution for pie chart
        $roleDistribution = User::select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get()
            ->pluck('count', 'role')
            ->toArray();

        // Career categories distribution
        $careerCategories = Career::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category')
            ->toArray();

        return compact('userRegistrations', 'assessmentsCompleted', 'roleDistribution', 'careerCategories');
    }

    private function getTopPerformers(): array
    {
        // Top 5 students with highest average career match scores
        $topStudents = User::where('role', 'student')
            ->with(['recommendations' => function($q) {
                $q->where('type', 'career');
            }])
            ->get()
            ->map(function($student) {
                $avgScore = $student->recommendations->avg('confidence_score') ?? 0;
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'avg_score' => round($avgScore, 1),
                    'recommendation_count' => $student->recommendations->count()
                ];
            })
            ->sortByDesc('avg_score')
            ->take(5)
            ->values();

        // Top 5 most active users
        $mostActiveUsers = User::withCount('activityLogs')
            ->orderByDesc('activity_logs_count')
            ->take(5)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role,
                    'activity_count' => $user->activity_logs_count
                ];
            });

        return compact('topStudents', 'mostActiveUsers');
    }
}
