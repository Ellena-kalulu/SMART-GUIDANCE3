@extends('layouts.dashboard')

@section('title', 'Analytics')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Teacher</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Analytics</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-black/80">Class Analytics</h1>
        <p class="text-black/50 text-sm mt-1">Insights into student performance and career interest trends.</p>
    </div>

    <!-- Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $total     = isset($students) ? $students->count() : 0;
            $completed = isset($students) ? $students->filter(fn($s) => $s->assessmentAttempts->where('status','completed')->isNotEmpty())->count() : 0;
            $avgScore  = isset($students) ? round($students->flatMap->academicResults->avg('score') ?? 0, 1) : 0;
            $pct       = $total > 0 ? round(($completed / $total) * 100) : 0;
        @endphp
        @foreach([
            ['Students Total', $total, 'text-brand-600'],
            ['Assessments Done', $completed, 'text-emerald-600'],
            ['Completion Rate', $pct . '%', 'text-blue-600'],
            ['Avg Academic Score', $avgScore . '%', 'text-amber-600'],
        ] as [$label, $val, $colorClass])
        <div class="card text-center">
            <p class="text-2xl font-bold {{ $colorClass }}">{{ $val }}</p>
            <p class="text-xs text-black/50 mt-1">{{ $label }}</p>
        </div>
        @endforeach
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="card">
            <h2 class="font-bold text-black/80 mb-4">Career Interest Distribution</h2>
            @if(!empty($analyticsData['career_interests']) && !isset($analyticsData['career_interests']['No data']))
            <canvas id="careerChart" height="260"></canvas>
            @else
            <div class="py-16 text-center text-black/40 text-sm">No career interest data yet. Students need to complete assessments first.</div>
            @endif
        </div>
        <div class="card">
            <h2 class="font-bold text-black/80 mb-4">Assessment Completion Status</h2>
            @if($total > 0)
            <canvas id="statusChart" height="260"></canvas>
            @else
            <div class="py-16 text-center text-black/40 text-sm">No students registered yet.</div>
            @endif
        </div>
    </div>

    <!-- Subject Performance -->
    <div class="card">
        <h2 class="font-bold text-black/80 mb-4">Average Score by Subject</h2>
        @if(!empty($analyticsData['subject_averages']) && !isset($analyticsData['subject_averages']['No data']))
        <canvas id="subjectChart" height="100"></canvas>
        @else
        <div class="py-12 text-center text-black/40 text-sm">No grade data uploaded yet.</div>
        @endif
    </div>

    <!-- Performance Tier Breakdown -->
    @php
        $allStudents = isset($students) ? $students : collect();
        $tiers = [
            'excellent' => ['label' => 'Excellent (≥80%)',  'color' => 'blue',   'count' => 0],
            'good'      => ['label' => 'Good (65–79%)',     'color' => 'blue',   'count' => 0],
            'average'   => ['label' => 'Average (50–64%)',  'color' => 'amber',  'count' => 0],
            'poor'      => ['label' => 'Needs Support (<50%)','color' => 'red',  'count' => 0],
        ];
        foreach ($allStudents as $s) {
            $a = $s->academicResults->avg('score');
            if ($a === null) continue;
            if ($a >= 80)      $tiers['excellent']['count']++;
            elseif ($a >= 65)  $tiers['good']['count']++;
            elseif ($a >= 50)  $tiers['average']['count']++;
            else               $tiers['poor']['count']++;
        }
        $gradedStudents = array_sum(array_column($tiers, 'count'));
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-6">
        <h2 class="font-bold text-black/80 mb-4">Performance Tier Breakdown</h2>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($tiers as $key => $tier)
            <div class="bg-{{ $tier['color'] }}-50 border border-{{ $tier['color'] }}-100 rounded-2xl p-4 text-center">
                <p class="text-3xl font-black text-{{ $tier['color'] }}-700">{{ $tier['count'] }}</p>
                <p class="text-xs font-semibold text-{{ $tier['color'] }}-600 mt-1">{{ $tier['label'] }}</p>
                @if($gradedStudents > 0)
                <p class="text-xs text-black/40 mt-0.5">{{ round($tier['count'] / $gradedStudents * 100) }}% of graded</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Subject Performance Detail -->
    @php
        $subjectBreakdown = [];
        foreach ($allStudents as $s) {
            foreach ($s->academicResults as $r) {
                $name = $r->subject?->name ?? 'Unknown';
                if (!isset($subjectBreakdown[$name])) $subjectBreakdown[$name] = ['total' => 0, 'count' => 0];
                $subjectBreakdown[$name]['total'] += $r->score;
                $subjectBreakdown[$name]['count']++;
            }
        }
        arsort($subjectBreakdown);
        $subjectAvgsFull = [];
        foreach ($subjectBreakdown as $name => $data) {
            $subjectAvgsFull[$name] = round($data['total'] / max(1, $data['count']), 1);
        }
        arsort($subjectAvgsFull);
    @endphp
    @if(!empty($subjectAvgsFull))
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">Subject Performance Detail</h2>
            <p class="text-xs text-black/40 mt-0.5">Average scores across all students per subject</p>
        </div>
        <div class="divide-y divide-black/5">
            @foreach($subjectAvgsFull as $subj => $avg)
            @php $color = $avg >= 70 ? 'blue' : ($avg >= 55 ? 'amber' : 'red'); @endphp
            <div class="px-6 py-3 flex items-center gap-4">
                <div class="w-2 h-2 rounded-full bg-{{ $color }}-500 shrink-0"></div>
                <span class="flex-1 text-sm font-medium text-black/70">{{ $subj }}</span>
                <div class="w-32 h-2 bg-black/[0.04] rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-{{ $color }}-500" style="width:{{ min(100, $avg) }}%"></div>
                </div>
                <span class="text-sm font-black text-{{ $color }}-600 w-12 text-right">{{ $avg }}%</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Top Performing + At Risk Students side by side -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Performers -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-black/10 flex items-center gap-2">
                <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                <h2 class="font-bold text-black/80">Top Performers</h2>
            </div>
            <div class="divide-y divide-black/10">
                @forelse($allStudents->sortByDesc(fn($s) => $s->academicResults->avg('score'))->take(5)->values() as $idx => $student)
                @php $avg = round($student->academicResults->avg('score') ?? 0, 1); @endphp
                <div class="px-6 py-4 flex items-center gap-4">
                    <span class="text-sm font-bold text-black/30 w-5">{{ $idx + 1 }}</span>
                    <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold text-sm shrink-0">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-black/80 text-sm truncate">{{ $student->name }}</p>
                        <p class="text-xs text-black/40">{{ $student->studentProfile?->form_level ?? '' }}</p>
                    </div>
                    <span class="font-bold text-blue-600 text-sm">{{ $avg }}%</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-black/40 text-sm">No data available.</div>
                @endforelse
            </div>
        </div>

        <!-- Students Needing Support -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-black/10 flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <h2 class="font-bold text-black/80">Students Needing Support (&lt;50%)</h2>
            </div>
            <div class="divide-y divide-black/10">
                @forelse($allStudents->filter(fn($s) => $s->academicResults->avg('score') !== null && $s->academicResults->avg('score') < 50)->sortBy(fn($s) => $s->academicResults->avg('score'))->take(5)->values() as $student)
                @php $avg = round($student->academicResults->avg('score') ?? 0, 1); @endphp
                <div class="px-6 py-4 flex items-center gap-4">
                    <div class="w-9 h-9 rounded-full bg-red-100 flex items-center justify-center text-red-700 font-bold text-sm shrink-0">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-black/80 text-sm truncate">{{ $student->name }}</p>
                        <p class="text-xs text-black/40">{{ $student->studentProfile?->form_level ?? '' }}</p>
                    </div>
                    <span class="font-bold text-red-600 text-sm">{{ $avg }}%</span>
                    <a href="{{ route('teacher.student.detail', $student) }}" class="px-2 py-1 bg-red-50 text-red-600 rounded-lg text-xs font-semibold hover:bg-red-100 transition-colors shrink-0">View →</a>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-black/40 text-sm">No students below 50% — great work!</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@push('scripts')
@php
    $chartCareerData   = $analyticsData['career_interests'] ?? [];
    $chartSubjectData  = $analyticsData['subject_averages'] ?? [];
    $chartCompleted    = $completed ?? 0;
    $chartPending      = max(0, ($total ?? 0) - $chartCompleted);
    $chartColors = ['#2563eb','#3b82f6','#60a5fa','#93c5fd','#1d4ed8','#1e40af','#dbeafe'];
@endphp
<script>
@if(!empty($chartCareerData) && !isset($chartCareerData['No data']))
const careerData = @json($chartCareerData);
new Chart(document.getElementById('careerChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(careerData),
        datasets: [{ data: Object.values(careerData), backgroundColor: @json($chartColors), borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
@endif

@if(($total ?? 0) > 0)
const completed = {{ $chartCompleted }};
const pending   = {{ $chartPending }};
new Chart(document.getElementById('statusChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: ['Completed', 'Pending'],
        datasets: [{ data: [completed, pending], backgroundColor: ['#2563eb','#e2e8f0'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});
@endif

@if(!empty($chartSubjectData) && !isset($chartSubjectData['No data']))
const subjectData = @json($chartSubjectData);
new Chart(document.getElementById('subjectChart').getContext('2d'), {
    type: 'bar',
    data: {
        labels: Object.keys(subjectData),
        datasets: [{
            label: 'Average Score (%)',
            data: Object.values(subjectData),
            backgroundColor: 'rgba(37,99,235,0.75)',
            borderRadius: 8, borderSkipped: false
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: { beginAtZero: true, max: 100, grid: { color: '#e2e8f0' } },
            x: { grid: { display: false } }
        },
        plugins: { legend: { display: false } }
    }
});
@endif
</script>
@endpush
@endsection
