@extends('layouts.dashboard')

@section('title', 'Analytics')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Counsellor</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Analytics</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,#fff 0%,transparent 60%)"></div>
        <div class="relative">
            <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Counsellor Portal</p>
            <h1 class="text-2xl md:text-3xl font-bold mb-1">School-Wide Analytics</h1>
            <p class="text-white/70 text-sm">Comprehensive guidance statistics for Luwinga Secondary School.</p>
        </div>
    </div>

    <!-- Summary Stats -->
    @php
        $total      = $analyticsData['total_students']          ?? 0;
        $assessed   = $analyticsData['completed_assessments']   ?? 0;
        $careers    = $analyticsData['total_career_recs']       ?? 0;
        $eligible   = $analyticsData['university_eligible']     ?? 0;
        $notAssessed = max(0, $total - $assessed);
        $assessRate  = $total > 0 ? round(($assessed / $total) * 100) : 0;
        $pendSessions= \App\Models\CounsellingSession::where('status','pending')->count();
        $completedSessions = \App\Models\CounsellingSession::where('status','completed')->count();
        $avgScore    = round(\App\Models\Recommendation::where('type','career')->avg('confidence_score') ?? 0);
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            [$total,          'Total Students',        'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z', 'bg-blue-600'],
            [$assessed,       'Assessments Done',      'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'bg-blue-500'],
            [$careers,        'Career Recs Generated', 'M13 10V3L4 14h7v7l9-11h-7z',                                                                                       'bg-blue-700'],
            [$eligible,       'University Eligible',   'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'bg-blue-800'],
        ] as [$val, $label, $icon, $bg])
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 {{ $bg }} rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-black text-blue-700">{{ $val }}</p>
                <p class="text-xs text-black/50 font-medium">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Key Counselling Indicators --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Assessment Coverage</p>
            <div class="flex items-end gap-3 mb-3">
                <p class="text-3xl font-black text-blue-700">{{ $assessRate }}%</p>
                <p class="text-xs text-black/40 mb-1">of students assessed</p>
            </div>
            <div class="h-3 bg-blue-50 rounded-full overflow-hidden border border-blue-100">
                <div class="h-full bg-blue-600 rounded-full" style="width:{{ $assessRate }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-black/40 mt-2">
                <span>{{ $assessed }} done</span>
                <span>{{ $notAssessed }} pending</span>
            </div>
        </div>

        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Counselling Sessions</p>
            <div class="space-y-2.5">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-black/60">Pending</span>
                    <span class="font-black text-amber-600 text-lg">{{ $pendSessions }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-black/60">Completed</span>
                    <span class="font-black text-blue-600 text-lg">{{ $completedSessions }}</span>
                </div>
                <div class="flex justify-between items-center pt-1 border-t border-black/5">
                    <span class="text-xs text-black/40">All Time Total</span>
                    <span class="font-bold text-black/60">{{ $pendSessions + $completedSessions }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white border border-blue-100 rounded-2xl p-5 shadow-sm">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Recommendation Quality</p>
            <div class="flex items-end gap-3 mb-3">
                <p class="text-3xl font-black text-blue-700">{{ $avgScore }}%</p>
                <p class="text-xs text-black/40 mb-1">avg confidence score</p>
            </div>
            @php
                $highConf = \App\Models\Recommendation::where('type','career')->where('confidence_score','>=',80)->count();
                $lowConf  = \App\Models\Recommendation::where('type','career')->where('confidence_score','<',50)->count();
            @endphp
            <div class="space-y-1.5 mt-1">
                <div class="flex justify-between text-xs">
                    <span class="text-black/50">High confidence (≥80%)</span>
                    <span class="font-bold text-blue-600">{{ $highConf }}</span>
                </div>
                <div class="flex justify-between text-xs">
                    <span class="text-black/50">Low confidence (&lt;50%)</span>
                    <span class="font-bold text-red-500">{{ $lowConf }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Form-Level Assessment Breakdown --}}
    <div class="bg-white rounded-2xl border border-black/10 shadow-sm p-6">
        <h2 class="font-bold text-black/80 mb-4">Assessment Status by Form</h2>
        @php
            $forms = ['Form 1', 'Form 2', 'Form 3', 'Form 4'];
            $formStats = [];
            foreach ($forms as $form) {
                $formStudents = \App\Models\StudentProfile::where('form_level', $form)->count();
                $formAssessed = \App\Models\StudentProfile::where('form_level', $form)
                    ->whereHas('user.assessmentAttempts', fn($q) => $q->where('status','completed'))
                    ->count();
                $formStats[$form] = ['total' => $formStudents, 'assessed' => $formAssessed, 'pct' => $formStudents > 0 ? round($formAssessed / $formStudents * 100) : 0];
            }
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach($formStats as $form => $stat)
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <p class="text-xs font-bold text-blue-700 uppercase">{{ $form }}</p>
                <p class="text-2xl font-black text-blue-800 my-1">{{ $stat['pct'] }}%</p>
                <p class="text-xs text-black/50">{{ $stat['assessed'] }} / {{ $stat['total'] }} assessed</p>
                <div class="mt-2 h-1.5 bg-blue-200 rounded-full overflow-hidden">
                    <div class="h-full bg-blue-600 rounded-full" style="width:{{ $stat['pct'] }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
            <h2 class="font-bold text-black/80 mb-4">Career Interest Distribution</h2>
            <canvas id="careerDist" height="260"></canvas>
        </div>
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
            <h2 class="font-bold text-black/80 mb-4">Students Needing Attention</h2>
            <canvas id="attentionChart" height="260"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
        <h2 class="font-bold text-black/80 mb-4">Assessment Completion Trend</h2>
        <canvas id="trendChart" height="100"></canvas>
    </div>

    <!-- Students Needing Guidance -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 flex items-center gap-2">
            <svg class="w-5 h-5 border-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h2 class="font-bold text-black/80">Students Needing Guidance</h2>
        </div>
        <div class="divide-y divide-black/10">
            @forelse($studentsNeedingAttention ?? [] as $student)
            @php $topRec = $student->recommendations->where('type','career')->sortByDesc('confidence_score')->first(); @endphp
            <div class="px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center bg-blue-700 font-bold shrink-0">
                        {{ strtoupper(substr($student->name, 0, 2)) }}
                    </div>
                    <div>
                        <p class="font-semibold text-black/80 text-sm">{{ $student->name }}</p>
                        <p class="text-xs text-black/50">{{ $student->studentProfile->form_level ?? '' }}</p>
                    </div>
                </div>
                <div class="flex-1 mx-6 text-sm text-black/60">
                    @if($student->assessmentAttempts->where('status','completed')->isEmpty())
                        Has not completed career assessment
                    @elseif($topRec && $topRec->confidence_score < 50)
                        Low match score ({{ round($topRec->confidence_score) }}%) — needs counselling
                    @else
                        Review recommended
                    @endif
                </div>
                <a href="{{ route('counsellor.student.detail', $student) }}"
                   class="px-4 py-2 bg-blue-600 text-white text-sm rounded-xl hover:bg-blue-700 transition-colors shrink-0">
                    View Profile
                </a>
            </div>
            @empty
            <div class="px-6 py-8 text-center text-black/40 text-sm">All students are on track.</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
@php
    $chartCareerDist  = $analyticsData['career_interests'] ?? ['Technology'=>35,'Health'=>25,'Business'=>20,'Engineering'=>12,'Arts'=>8];
    $chartOnTrack     = $analyticsData['on_track']         ?? 0;
    $chartNeedsAttn   = $analyticsData['needs_attention']  ?? 0;
    $chartMonths      = $analyticsData['trend_labels']     ?? ['Jan','Feb','Mar','Apr','May','Jun'];
    $chartCounts      = $analyticsData['trend_data']       ?? [20,35,50,65,80,95];
@endphp
<script>
const careerDist = @json($chartCareerDist);
new Chart(document.getElementById('careerDist').getContext('2d'), {
    type: 'doughnut',
    data: {
        labels: Object.keys(careerDist),
        datasets: [{ data: Object.values(careerDist), backgroundColor: ['#3b82f6','#2563eb','#2563eb','#000000','#2563eb'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

const onTrack   = {{ $chartOnTrack }};
const needsAttn = {{ $chartNeedsAttn }};
new Chart(document.getElementById('attentionChart').getContext('2d'), {
    type: 'pie',
    data: {
        labels: ['On Track', 'Needs Attention'],
        datasets: [{ data: [onTrack, needsAttn], backgroundColor: ['#2563eb','#2563eb'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

const months = @json($chartMonths);
const counts  = @json($chartCounts);
new Chart(document.getElementById('trendChart').getContext('2d'), {
    type: 'line',
    data: {
        labels: months,
        datasets: [{ label: 'Assessments Completed', data: counts,
            borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.1)', fill: true, tension: 0.4 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true }, x: { grid: { display: false } } } }
});
</script>
@endpush
@endsection
