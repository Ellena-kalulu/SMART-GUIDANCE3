@extends('layouts.dashboard')
@section('title', 'System Reports')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Reports</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-black">System Reports</h1>
            <p class="text-black/50 text-sm mt-0.5">Overview of system usage and data distribution</p>
        </div>
        <span class="text-xs text-black/40">Last updated: {{ now()->format('d M Y, H:i') }}</span>
    </div>

    {{-- Summary stats --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        @foreach([
            ['Total Users',          $stats['total_users'],         'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['Total Careers',        $stats['total_careers'],        'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
            ['Total Subjects',       $stats['total_subjects'],       'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['University Programs',  $stats['total_universities'],   'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
            ['Subject Combinations', $stats['subject_combinations'], 'M4 6h16M4 10h16M4 14h16M4 18h16'],
        ] as [$label, $val, $icon])
        <div class="bg-white rounded-2xl border border-blue-100 p-4 text-center">
            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center mx-auto mb-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
            </div>
            <p class="text-2xl font-black text-blue-700">{{ $val }}</p>
            <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Charts row --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Users by role --}}
        <div class="bg-white rounded-2xl border border-black/15 p-6">
            <h2 class="font-bold text-black/80 mb-1">Users by Role</h2>
            <p class="text-xs text-black/40 mb-5">Distribution of all registered accounts</p>
            <canvas id="roleChart" height="240"></canvas>
        </div>

        {{-- User growth --}}
        <div class="bg-white rounded-2xl border border-black/15 p-6">
            <h2 class="font-bold text-black/80 mb-1">User Registration Trend</h2>
            <p class="text-xs text-black/40 mb-5">New accounts per month (last 12 months)</p>
            <canvas id="growthChart" height="240"></canvas>
        </div>
    </div>

    {{-- Users by role table --}}
    <div class="bg-white rounded-2xl border border-black/15 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">Active Users by Role</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white border-b border-black/10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">% of Users</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Distribution</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @php $totalU = $activeUsers->sum('count'); @endphp
                    @foreach($activeUsers as $row)
                    @php
                        $pct = $totalU > 0 ? round(($row->count / $totalU) * 100) : 0;
                    @endphp
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                {{ ucfirst($row->role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 font-bold text-black/80">{{ $row->count }}</td>
                        <td class="px-6 py-4 text-black/60">{{ $pct }}%</td>
                        <td class="px-6 py-4 w-48">
                            <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                                <div class="h-full bg-blue-500 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- User growth table --}}
    @if($userGrowth->isNotEmpty())
    <div class="bg-white rounded-2xl border border-black/15 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center justify-between">
            <h2 class="font-bold text-black/80">Monthly Registration History</h2>
            <span class="text-xs text-black/40">Last {{ $userGrowth->count() }} months</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white border-b border-black/10">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Month</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">New Users</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-black/50 uppercase">Trend</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/10">
                    @foreach($userGrowth as $i => $row)
                    @php
                        $prev = $i > 0 ? $userGrowth[$i - 1]->count : null;
                        $trend = $prev ? ($row->count > $prev ? 'up' : ($row->count < $prev ? 'down' : 'same')) : 'same';
                    @endphp
                    <tr class="hover:bg-white">
                        <td class="px-6 py-3 text-sm font-medium text-black/70">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $row->month)->format('F Y') }}
                        </td>
                        <td class="px-6 py-3 font-bold text-black/80">{{ $row->count }}</td>
                        <td class="px-6 py-3">
                            @if($trend === 'up')
                            <span class="flex items-center gap-1 text-xs font-semibold text-blue-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                                Growth
                            </span>
                            @elseif($trend === 'down')
                            <span class="flex items-center gap-1 text-xs font-semibold text-blue-600">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                Decline
                            </span>
                            @else
                            <span class="text-xs text-black/35">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

@push('scripts')
<script>
// Users by role — doughnut
const roleCtx = document.getElementById('roleChart').getContext('2d');
new Chart(roleCtx, {
    type: 'doughnut',
    data: {
        labels: {!! $activeUsers->pluck('role')->map(fn($r) => ucfirst($r))->toJson() !!},
        datasets: [{
            data: {!! $activeUsers->pluck('count')->toJson() !!},
            backgroundColor: ['#2563eb','#3b82f6','#60a5fa','#93c5fd','#bfdbfe'],
            borderWidth: 0,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: {
            legend: { position: 'bottom', labels: { padding: 16, font: { size: 12 } } }
        }
    }
});

// User growth — line
const growthCtx = document.getElementById('growthChart').getContext('2d');
new Chart(growthCtx, {
    type: 'line',
    data: {
        labels: {!! $userGrowth->map(fn($r) => \Carbon\Carbon::createFromFormat('Y-m', $r->month)->format('M Y'))->toJson() !!},
        datasets: [{
            label: 'New Users',
            data: {!! $userGrowth->pluck('count')->toJson() !!},
            borderColor: '#2563eb',
            backgroundColor: 'rgba(37,99,235,0.08)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#2563eb',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.06)' } },
            x: { grid: { display: false } }
        }
    }
});
</script>
@endpush
@endsection
