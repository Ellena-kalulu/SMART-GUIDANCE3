@extends('layouts.dashboard')

@section('title', 'Analytics Dashboard')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Analytics Dashboard</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-500 to-blue-700 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Analytics Dashboard</h1>
        <p class="text-blue-100">Comprehensive system insights and performance metrics</p>
    </div>

    <!-- Quick Stats Row -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($userStats['totalUsers']) }}</p>
                    <p class="text-xs text-blue-600 mt-1">+{{ number_format($userStats['newUsersThisMonth']) }} this month</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Active Users</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($userStats['activeUsers']) }}</p>
                    <p class="text-xs text-black/50 mt-1">{{ number_format($userStats['inactiveUsers']) }} inactive</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Assessment Rate</p>
                    <p class="text-3xl font-bold text-black/80">{{ $assessmentStats['assessmentRate'] }}%</p>
                    <p class="text-xs text-black/50 mt-1">{{ number_format($assessmentStats['studentsWithAssessment']) }}/{{ number_format($assessmentStats['totalStudents']) }} students</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Avg Match Score</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($recommendationStats['avgConfidenceScore'], 1) }}%</p>
                    <p class="text-xs text-black/50 mt-1">{{ number_format($recommendationStats['totalRecommendations']) }} recommendations</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Registration Trend -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">User Registration Trend (Last 12 Months)</h3>
            <canvas id="userRegistrationChart" height="250"></canvas>
        </div>

        <!-- Assessment Completion Trend -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Assessment Completion Trend (Last 12 Months)</h3>
            <canvas id="assessmentChart" height="250"></canvas>
        </div>
    </div>

    <!-- Second Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Role Distribution -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">User Role Distribution</h3>
            <canvas id="roleChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($chartData['roleDistribution'] as $role => $count)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ ucfirst($role) }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Career Category Distribution -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-black/80 mb-4">Career Category Distribution</h3>
            <canvas id="careerChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($chartData['careerCategories'] as $category => $count)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ $category }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Students -->
        <div class="bg-white rounded-xl shadow-sm border border-black/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-black/10 bg-gradient-to-r border-blue-50 border-blue-50">
                <h3 class="font-bold text-black/80">Top Performing Students</h3>
                <p class="text-xs text-black/50 mt-1">Based on average career match score</p>
            </div>
            <div class="divide-y divide-black/10">
                @foreach($topPerformers['topStudents'] as $index => $student)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full
                            {{ $index == 0 ? 'bg-blue-100 bg-blue-700' :
                               ($index == 1 ? 'bg-black/[0.03] text-black/70' :
                               ($index == 2 ? 'bg-blue-100 bg-blue-700' : 'bg-blue-100 text-blue-700')) }}
                            flex items-center justify-center font-bold">
                            {{ $index + 1 }}
                        </div>
                        <div>
                            <p class="font-medium text-black/80">{{ $student['name'] }}</p>
                            <p class="text-xs text-black/50">{{ $student['recommendation_count'] }} recommendations</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-blue-600">{{ $student['avg_score'] }}%</p>
                        <p class="text-xs text-black/40">Match Score</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Most Active Users -->
        <div class="bg-white rounded-xl shadow-sm border border-black/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-black/10 bg-gradient-to-r from-blue-50 border-blue-50">
                <h3 class="font-bold text-black/80">Most Active Users</h3>
                <p class="text-xs text-black/50 mt-1">Based on total activity count</p>
            </div>
            <div class="divide-y divide-black/10">
                @foreach($topPerformers['mostActiveUsers'] as $user)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div>
                        <p class="font-medium text-black/80">{{ $user['name'] }}</p>
                        <p class="text-xs text-black/50 capitalize">{{ $user['role'] }}</p>
                    </div>
                    <div class="text-right">
                        <p class="font-bold text-blue-600">{{ number_format($user['activity_count']) }}</p>
                        <p class="text-xs text-black/40">actions</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- System Health -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
        <h3 class="font-bold text-black/80 mb-4">System Health</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
            <div class="text-center p-4 bg-white rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($systemStats['totalLogins']) }}</div>
                <div class="text-xs text-black/50 mt-1">Total Logins</div>
            </div>
            <div class="text-center p-4 bg-white rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($systemStats['totalReportsDownloaded']) }}</div>
                <div class="text-xs text-black/50 mt-1">Reports Downloaded</div>
            </div>
            <div class="text-center p-4 bg-white rounded-xl">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($systemStats['totalAssessmentsCompleted']) }}</div>
                <div class="text-xs text-black/50 mt-1">Assessments</div>
            </div>
        </div>
    </div>

    <!-- Explore Detailed Analytics -->
    <div>
        <h2 class="text-base font-bold text-black/80 mb-4">Explore Detailed Analytics</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <a href="{{ route('admin.analytics.users') }}"
               class="group bg-white rounded-2xl border border-black/15 p-5 hover:border-blue-400 hover:shadow-md transition-all">
                <div class="w-11 h-11 bg-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-black/80 mb-1">User Analytics</h3>
                <p class="text-xs text-black/50 leading-relaxed">Registration trends, role breakdown, and active vs inactive user metrics.</p>
                <div class="mt-4 flex items-center gap-1 text-blue-600 text-xs font-semibold">
                    View details
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('admin.analytics.assessments') }}"
               class="group bg-white rounded-2xl border border-black/15 p-5 hover:border-blue-500 hover:shadow-md transition-all">
                <div class="w-11 h-11 border-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-black/80 mb-1">Assessment Analytics</h3>
                <p class="text-xs text-black/50 leading-relaxed">Completion rates, monthly trends, and student participation statistics.</p>
                <div class="mt-4 flex items-center gap-1 bg-blue-600 text-xs font-semibold">
                    View details
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('admin.analytics.careers') }}"
               class="group bg-white rounded-2xl border border-black/15 p-5 hover:border-blue-500 hover:shadow-md transition-all">
                <div class="w-11 h-11 border-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-black/80 mb-1">Career Analytics</h3>
                <p class="text-xs text-black/50 leading-relaxed">Top recommended careers, category distribution, and match score insights.</p>
                <div class="mt-4 flex items-center gap-1 bg-blue-600 text-xs font-semibold">
                    View details
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>

            <a href="{{ route('admin.analytics.system') }}"
               class="group bg-white rounded-2xl border border-black/15 p-5 hover:border-blue-500 hover:shadow-md transition-all">
                <div class="w-11 h-11 border-blue-100 rounded-xl flex items-center justify-center mb-4 group-hover:bg-blue-200 transition-colors">
                    <svg class="w-5 h-5 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2v-4M9 21H5a2 2 0 01-2-2v-4m0 0h18"/>
                    </svg>
                </div>
                <h3 class="font-semibold text-black/80 mb-1">System Analytics</h3>
                <p class="text-xs text-black/50 leading-relaxed">Login activity, chat interactions, report downloads, and system health.</p>
                <div class="mt-4 flex items-center gap-1 bg-blue-600 text-xs font-semibold">
                    View details
                    <svg class="w-3.5 h-3.5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Registration Chart
    const regCtx = document.getElementById('userRegistrationChart').getContext('2d');
    new Chart(regCtx, {
        type: 'line',
        data: {
            labels: @json(array_keys($chartData['userRegistrations'])),
            datasets: [{
                label: 'New Users',
                data: @json(array_values($chartData['userRegistrations'])),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    // Assessment Chart
    const assessCtx = document.getElementById('assessmentChart').getContext('2d');
    new Chart(assessCtx, {
        type: 'bar',
        data: {
            labels: @json(array_keys($chartData['assessmentsCompleted'])),
            datasets: [{
                label: 'Assessments Completed',
                data: @json(array_values($chartData['assessmentsCompleted'])),
                backgroundColor: '#2563eb',
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } }
        }
    });

    // Role Distribution Chart
    const roleCtx = document.getElementById('roleChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($chartData['roleDistribution'])),
            datasets: [{
                data: @json(array_values($chartData['roleDistribution'])),
                backgroundColor: ['#3b82f6', '#2563eb', '#000000', '#2563eb', '#2563eb'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Career Distribution Chart
    const careerCtx = document.getElementById('careerChart').getContext('2d');
    new Chart(careerCtx, {
        type: 'bar',
        data: {
            labels: @json(array_keys($chartData['careerCategories'])),
            datasets: [{
                label: 'Number of Careers',
                data: @json(array_values($chartData['careerCategories'])),
                backgroundColor: '#2563eb',
                borderRadius: 8,
                barPercentage: 0.7
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: { legend: { position: 'top' } },
            scales: { y: { beginAtZero: true, grid: { color: '#000000' } } }
        }
    });
});
</script>
@endpush
@endsection
