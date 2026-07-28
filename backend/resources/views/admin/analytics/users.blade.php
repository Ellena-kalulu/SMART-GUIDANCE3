@extends('layouts.dashboard')

@section('title', 'User Analytics')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <a href="{{ route('admin.analytics.index') }}" class="text-black/50 hover:text-blue-600">Analytics</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">User Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 bg-blue-600 rounded-2xl p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">User Analytics</h1>
        <p class="text-blue-100">Comprehensive user behavior and demographic insights</p>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5">
        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Total Users</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format(array_sum($roleDistribution)) }}</p>
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
                    <p class="text-3xl font-bold text-black/80">{{ number_format($activeStatus[1] ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 text-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">Students</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($roleDistribution['student'] ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 shadow-sm border border-black/10">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-black/50 text-sm">This Month</p>
                    <p class="text-3xl font-bold text-black/80">{{ number_format($registrationTrends->sum('total') ?? 0) }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 bg-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- User Growth Trend -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-slate-800 mb-4">User Growth Over Time</h3>
            <canvas id="userGrowthChart" height="250"></canvas>
        </div>

        <!-- Role Distribution Pie -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-slate-800 mb-4">Role Distribution</h3>
            <canvas id="rolePieChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($roleDistribution as $role => $count)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ ucfirst($role) }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($count) }} ({{ round(($count / array_sum($roleDistribution)) * 100) }}%)</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Charts Row 2 -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Level Distribution -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-slate-800 mb-4">Students by Form Level</h3>
            <canvas id="formChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($formDistribution as $item)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ $item->form_level ?? 'Not Set' }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($item->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Stream Distribution -->
        <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
            <h3 class="font-bold text-slate-800 mb-4">Students by Stream</h3>
            <canvas id="streamChart" height="250"></canvas>
            <div class="mt-4 grid grid-cols-2 gap-2">
                @foreach($streamDistribution as $item)
                <div class="flex justify-between items-center px-3 py-2 bg-white rounded-lg">
                    <span class="text-sm font-medium text-black/60">{{ $item->stream ?? 'Not Set' }}</span>
                    <span class="text-sm font-bold text-black/80">{{ number_format($item->count) }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Registration Trends by Role -->
    <div class="bg-white rounded-xl p-6 shadow-sm border border-black/10">
        <h3 class="font-bold text-slate-800 mb-4">Registration Trends by Role (Last 12 Months)</h3>
        <canvas id="registrationTrendsChart" height="300"></canvas>
    </div>

    <!-- Recent Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 bg-white">
            <h3 class="font-bold text-slate-800">Recently Active Users</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">User</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Activities</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Assessments</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50">Joined</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @foreach($recentUsers as $user)
                    <tr class="hover:bg-white">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-100 to-blue-200 flex items-center justify-center text-blue-700 font-semibold text-sm">
                                    {{ strtoupper(substr($user->name, 0, 2)) }}
                                </div>
                                <div>
                                    <p class="font-medium text-black/80">{{ $user->name }}</p>
                                    <p class="text-xs text-black/40">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold capitalize
                                {{ $user->role === 'admin' ? 'text-blue-100 text-blue-700' :
                                   ($user->role === 'teacher' ? 'text-blue-100 text-blue-700' :
                                   ($user->role === 'counsellor' ? 'bg-blue-100 bg-blue-700' :
                                   ($user->role === 'parent' ? 'bg-blue-100 bg-blue-700' : 'bg-blue-100 text-blue-700'))) }}">
                                {{ $user->role }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-black/60">{{ number_format($user->activity_logs_count) }}</td>
                        <td class="px-6 py-4 text-black/60">{{ number_format($user->assessment_attempts_count) }}</td>
                        <td class="px-6 py-4 text-black/50 text-sm">{{ $user->created_at->format('M d, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Growth Chart
    const growthCtx = document.getElementById('userGrowthChart').getContext('2d');
    new Chart(growthCtx, {
        type: 'line',
        data: {
            labels: @json($registrationTrends->pluck('month')),
            datasets: [{
                label: 'Total Registrations',
                data: @json($registrationTrends->pluck('total')),
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });

    // Role Pie Chart
    const roleCtx = document.getElementById('rolePieChart').getContext('2d');
    new Chart(roleCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($roleDistribution)),
            datasets: [{
                data: @json(array_values($roleDistribution)),
                backgroundColor: ['#3b82f6', '#2563eb', '#2563eb', '#000000', '#2563eb'],
                borderWidth: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, plugins: { legend: { position: 'bottom' } } }
    });

    // Form Level Chart
    const formCtx = document.getElementById('formChart').getContext('2d');
    new Chart(formCtx, {
        type: 'bar',
        data: {
            labels: @json($formDistribution->pluck('form_level')),
            datasets: [{
                label: 'Number of Students',
                data: @json($formDistribution->pluck('count')),
                backgroundColor: '#2563eb',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
    });

    // Stream Chart
    const streamCtx = document.getElementById('streamChart').getContext('2d');
    new Chart(streamCtx, {
        type: 'bar',
        data: {
            labels: @json($streamDistribution->pluck('stream')),
            datasets: [{
                label: 'Number of Students',
                data: @json($streamDistribution->pluck('count')),
                backgroundColor: '#2563eb',
                borderRadius: 8
            }]
        },
        options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
    });

    // Registration Trends by Role
    const trendsCtx = document.getElementById('registrationTrendsChart').getContext('2d');
    new Chart(trendsCtx, {
        type: 'line',
        data: {
            labels: @json($registrationTrends->pluck('month')),
            datasets: [
                { label: 'Students', data: @json($registrationTrends->pluck('students')), borderColor: '#3b82f6', tension: 0.4, fill: false },
                { label: 'Teachers', data: @json($registrationTrends->pluck('teachers')), borderColor: '#2563eb', tension: 0.4, fill: false },
                { label: 'Counsellors', data: @json($registrationTrends->pluck('counsellors')), borderColor: '#2563eb', tension: 0.4, fill: false },
                { label: 'Parents', data: @json($registrationTrends->pluck('parents')), borderColor: '#000000', tension: 0.4, fill: false }
            ]
        },
        options: { responsive: true, maintainAspectRatio: true }
    });
});
</script>
@endpush
@endsection
