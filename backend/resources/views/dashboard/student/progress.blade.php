@extends('layouts.dashboard')

@section('title', 'Academic Progress')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
    <span class="font-semibold text-black/80">Academic Progress</span>
@endsection

@section('content')
@php
    $rpt        = $academicReport ?? [];
    $overview   = $rpt['overview'] ?? [];
    $subProg    = $rpt['subject_progress'] ?? [];      // [{subject, forms:{Form1:score,...}, trend}]
    $sw         = $rpt['strengths_weaknesses'] ?? [];
    $trendAnal  = $rpt['trend_analysis'] ?? [];
    $health     = $rpt['academic_health'] ?? [];
    $preds      = $rpt['predictions'] ?? [];
    $milestones = $rpt['milestones'] ?? [];
    $advice     = $rpt['improvement_advice'] ?? [];
    $roadmap    = $rpt['improvement_roadmap'] ?? [];
    $warnings   = $rpt['early_warnings'] ?? [];
    $msceComp   = $rpt['combination_comparison'] ?? [];

    $overallAvg    = $overview['overall_average'] ?? null;
    $perfLevel     = $overview['performance_level'] ?? 'No Data';
    $trendDir      = $overview['trend_direction'] ?? 'stable';
    $recPath       = str_replace(' Path', '', $overview['recommended_path'] ?? '');
    $healthLevel   = $health['level'] ?? 'Unknown';
    $hasData       = $allResults->isNotEmpty();

    // Best and worst subject from subject_progress
    $bestSub  = collect($subProg)->sortByDesc(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? $r['forms']['Form 2'] ?? 0)->first();
    $worstSub = collect($subProg)->filter(fn($r) => ($r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? null) !== null)
                    ->sortBy(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? 100)->first();

    // Form averages for Journey Timeline (computed from allResults)
    $formAvgs = collect(['Form 1','Form 2','Form 3','Form 4'])->mapWithKeys(function($f) use ($allResults) {
        $scores = $allResults->where('form_level', $f)->pluck('score');
        return [$f => $scores->isNotEmpty() ? round($scores->avg(), 1) : null];
    });

    $journeyLabels = [
        'Form 1' => fn($avg) => $avg >= 70 ? 'Good foundation' : ($avg >= 50 ? 'Steady start' : 'Building skills'),
        'Form 2' => fn($avg) => $avg >= 80 ? 'Strong improvement' : ($avg >= 65 ? 'Steady progress' : 'Keep working'),
        'Form 3' => fn($avg) => $avg >= 85 ? 'Excellent performance' : ($avg >= 70 ? 'Good momentum' : 'Focus needed'),
        'Form 4' => fn($avg) => $avg >= 90 ? 'Ready for ' . $recPath . ' Combination' : ($avg >= 75 ? 'On track' : 'More effort needed'),
    ];

    // Badges earned
    $badges = [];
    if ($overallAvg >= 90) $badges[] = ['icon' => '🏅', 'label' => 'Top Performer'];
    if (!empty($sw['strengths'])) $badges[] = ['icon' => '🌟', 'label' => $sw['strengths'][0] . ' Star'];
    $improving = collect($trendAnal)->where('trend', 'Improving')->count();
    if ($improving >= 3) $badges[] = ['icon' => '📈', 'label' => 'Consistent Improver'];
    if ($recPath === 'Science') $badges[] = ['icon' => '🔬', 'label' => 'Science Path'];
    if ($recPath === 'Humanities') $badges[] = ['icon' => '📚', 'label' => 'Humanities Path'];
    $bestChange = collect($trendAnal)->sortByDesc('change')->first();
    if ($bestChange && $bestChange['change'] >= 10) $badges[] = ['icon' => '🚀', 'label' => 'Most Improved: ' . $bestChange['subject']];

    // Risk from early warnings
    $riskLevel = match(true) {
        count($warnings) === 0        => 'Low',
        count($warnings) <= 2         => 'Medium',
        default                       => 'High',
    };

@endphp

<div class="max-w-4xl mx-auto space-y-6">

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 1. ACADEMIC SUMMARY DASHBOARD                                              --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-black/8">
    <div class="px-6 py-5" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Academic Progress</p>
                <h1 class="text-2xl font-bold text-white">Welcome, {{ $user->name }}!</h1>
                <p class="text-blue-100/70 text-xs mt-1">Grades are uploaded by your teacher and update automatically.</p>
            </div>
            <div class="flex gap-2">
                <div x-data="{open:false}" class="relative">
                    <button @click="open=!open" class="px-3 py-2 bg-white/20 hover:bg-white/30 text-white rounded-xl text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Download
                    </button>
                    <div x-show="open" @click.away="open=false" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-xl shadow-lg border border-black/10 py-1 z-20">
                        <a href="{{ route('student.reports.academic-progress') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-black/70 hover:bg-blue-50/50">📊 Academic Progress</a>
                        <a href="{{ route('student.reports.development-plan') }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-black/70 hover:bg-blue-50/50">🗺 Development Plan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($hasData)
    <div class="grid grid-cols-2 sm:grid-cols-5 divide-x divide-y sm:divide-y-0 divide-black/[0.06]">
        <div class="p-5 text-center">
            <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Overall</p>
            <p class="text-3xl font-black {{ $overallAvg >= 75 ? 'text-blue-600' : ($overallAvg >= 55 ? 'text-amber-600' : 'text-rose-500') }}">
                {{ $overallAvg !== null ? $overallAvg . '%' : '—' }}
            </p>
            <p class="text-xs font-semibold text-black/45 mt-0.5">{{ $perfLevel }}</p>
        </div>
        <div class="p-5 text-center">
            <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Trend</p>
            <p class="text-2xl">{{ $trendDir === 'up' ? '📈' : ($trendDir === 'down' ? '📉' : '➡️') }}</p>
            <p class="text-xs font-semibold text-black/45 mt-0.5">
                {{ $trendDir === 'up' ? 'Improving' : ($trendDir === 'down' ? 'Declining' : 'Stable') }}
            </p>
        </div>
        <div class="p-5 text-center">
            <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Best Subject</p>
            <p class="text-sm font-black text-blue-700">{{ $bestSub['subject'] ?? '—' }}</p>
            @php $bs = $bestSub ? ($bestSub['forms']['Form 4'] ?? $bestSub['forms']['Form 3'] ?? null) : null; @endphp
            @if($bs) <p class="text-xs text-blue-500 font-semibold">{{ $bs }}%</p> @endif
        </div>
        <div class="p-5 text-center">
            @php
                $ws = $worstSub ? ($worstSub['forms']['Form 4'] ?? $worstSub['forms']['Form 3'] ?? null) : null;
                $needsAttention = $ws !== null && $ws < 65;
            @endphp
            <p class="text-[10px] font-bold text-black/35 uppercase mb-1">{{ $needsAttention ? 'Needs Attention' : 'Lowest Subject' }}</p>
            <p class="text-sm font-black {{ $needsAttention ? 'text-rose-500' : 'text-amber-500' }}">{{ $worstSub['subject'] ?? '—' }}</p>
            @if($ws) <p class="text-xs {{ $needsAttention ? 'text-rose-400' : 'text-amber-400' }} font-semibold">{{ $ws }}%</p> @endif
        </div>
        <div class="p-5 text-center col-span-2 sm:col-span-1">
            <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Recommended</p>
            <p class="text-sm font-black text-brand-700">{{ $recPath ?: '—' }}</p>
            <p class="text-xs text-black/40">Combination</p>
        </div>
    </div>
    @else
    <div class="px-6 py-10 text-center">
        <p class="text-4xl mb-3">📋</p>
        <p class="font-bold text-black/70 mb-1">No grades yet</p>
        <p class="text-sm text-black/45">Your teacher has not uploaded any grades. They will appear here automatically.</p>
    </div>
    @endif
</div>

@if($hasData)

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 2. ACADEMIC JOURNEY TIMELINE                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
    <h2 class="font-bold text-black/80 mb-6">Your Academic Journey</h2>
    <div class="flex items-stretch gap-0 overflow-x-auto pb-2">
        @foreach(['Form 1','Form 2','Form 3','Form 4'] as $fi => $form)
        @php
            $avg = $formAvgs[$form];
            $hasForm = $avg !== null;
            $label = $hasForm ? ($journeyLabels[$form])($avg) : 'No data yet';
            $isLast = $fi === 3;
            $color = $hasForm ? ($avg >= 80 ? 'blue' : ($avg >= 60 ? 'blue' : 'amber')) : 'slate';
        @endphp
        <div class="flex-1 min-w-[120px] flex flex-col items-center relative">
            {{-- Connector line --}}
            @if(!$isLast)
            <div class="absolute top-8 left-1/2 w-full h-0.5 {{ $hasForm ? 'bg-brand-200' : 'bg-slate-200' }}"></div>
            @endif
            {{-- Circle --}}
            <div class="relative z-10 w-16 h-16 rounded-full flex flex-col items-center justify-center text-center shadow-sm
                {{ $hasForm ? "bg-{$color}-100 border-2 border-{$color}-400" : 'bg-slate-100 border-2 border-slate-200' }}">
                @if($hasForm)
                <span class="font-black text-base text-{{ $color }}-700">{{ $avg }}%</span>
                @else
                <span class="text-slate-400 text-xs font-bold">—</span>
                @endif
            </div>
            {{-- Label --}}
            <p class="text-xs font-bold {{ $hasForm ? 'text-black/70' : 'text-black/25' }} mt-2">{{ $form }}</p>
            <p class="text-[10px] text-black/40 text-center px-1 mt-0.5 leading-tight">{{ $label }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 3. SUBJECT GROWTH CARDS                                                    --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($subProg))
<div>
    <h2 class="font-bold text-black/80 mb-4">Subject Growth</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($subProg as $row)
        @php
            $latest = $row['forms']['Form 4'] ?? $row['forms']['Form 3'] ?? $row['forms']['Form 2'] ?? $row['forms']['Form 1'] ?? null;
            $first  = array_filter($row['forms']);
            $first  = reset($first) ?: null;
            $change = ($latest !== null && $first !== null) ? round($latest - $first, 0) : null;
            $sc     = $latest ?? 0;
            $label  = $sc >= 90 ? 'Excellent' : ($sc >= 75 ? 'Very Good' : ($sc >= 60 ? 'Good' : ($sc >= 45 ? 'Average' : 'Needs Work')));
            $col    = $sc >= 75 ? 'blue' : ($sc >= 55 ? 'amber' : 'rose');
        @endphp
        <div class="bg-white rounded-2xl border border-black/[0.06] shadow-sm p-4">
            <p class="text-xs font-bold text-black/45 mb-2 truncate">{{ $row['subject'] }}</p>
            <p class="text-2xl font-black {{ $latest !== null ? "text-{$col}-600" : 'text-black/25' }}">
                {{ $latest !== null ? $latest . '%' : '—' }}
            </p>
            <p class="text-xs font-semibold text-black/50 mt-0.5">{{ $latest !== null ? $label : 'No grade' }}</p>
            @if($change !== null)
            <p class="text-xs font-bold mt-2 {{ $change >= 0 ? 'text-blue-500' : 'text-rose-500' }}">
                {{ $change >= 0 ? '📈 +' . $change . '%' : '📉 ' . $change . '%' }} from Form 1
            </p>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 4. ACADEMIC HEALTH METER                                                   --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h2 class="font-bold text-black/80 mb-4">Academic Health</h2>
    @php
        $hl    = $healthLevel;
        $hcol  = ['Excellent' => 'blue', 'Good' => 'blue', 'Average' => 'amber', 'At Risk' => 'rose', 'Unknown' => 'slate'][$hl] ?? 'slate';
        $hItems = [
            ['label' => 'Performance',  'val' => $perfLevel,   'ok' => in_array($perfLevel, ['Excellent','Good'])],
            ['label' => 'Trend',        'val' => $trendDir === 'up' ? 'Improving' : ($trendDir === 'down' ? 'Declining' : 'Stable'), 'ok' => $trendDir !== 'down'],
            ['label' => 'Strengths',    'val' => count($sw['strengths'] ?? []) . ' subjects ≥70%', 'ok' => count($sw['strengths'] ?? []) > 0],
            ['label' => 'Weaknesses',   'val' => count($sw['weaknesses'] ?? []) . ' subjects below 55%', 'ok' => count($sw['weaknesses'] ?? []) === 0],
        ];
    @endphp
    <div class="flex items-start gap-5">
        <div class="w-20 h-20 rounded-full bg-{{ $hcol }}-100 border-4 border-{{ $hcol }}-400 flex flex-col items-center justify-center shrink-0">
            <span class="text-{{ $hcol }}-700 font-black text-xs uppercase">{{ $hl }}</span>
        </div>
        <div class="flex-1 grid grid-cols-2 gap-3">
            @foreach($hItems as $item)
            <div class="flex items-center gap-2">
                <span class="w-4 h-4 rounded-full flex items-center justify-center shrink-0 {{ $item['ok'] ? 'bg-blue-100' : 'bg-rose-100' }}">
                    <svg class="w-2.5 h-2.5 {{ $item['ok'] ? 'text-blue-500' : 'text-rose-400' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        @if($item['ok'])
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        @else
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                        @endif
                    </svg>
                </span>
                <div>
                    <p class="text-[10px] font-bold text-black/35 uppercase">{{ $item['label'] }}</p>
                    <p class="text-xs font-semibold text-black/70">{{ $item['val'] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 5. PERFORMANCE HEATMAP                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($subProg))
<div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b bg-blue-50/40">
        <h2 class="font-bold text-black/80">Performance Heatmap</h2>
        <p class="text-xs text-black/45 mt-0.5">
            <span class="inline-flex items-center gap-1">🟢 75%+</span>
            <span class="ml-2 inline-flex items-center gap-1">🟡 55–74%</span>
            <span class="ml-2 inline-flex items-center gap-1">🟠 45–54%</span>
            <span class="ml-2 inline-flex items-center gap-1">🔴 Below 45%</span>
        </p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[440px] text-sm">
            <thead>
                <tr class="text-[10px] font-bold text-black/35 uppercase">
                    <th class="px-4 py-3 text-left">Subject</th>
                    <th class="px-3 py-3 text-center">Form 1</th>
                    <th class="px-3 py-3 text-center">Form 2</th>
                    <th class="px-3 py-3 text-center">Form 3</th>
                    <th class="px-3 py-3 text-center">Form 4</th>
                    <th class="px-3 py-3 text-center">Trend</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/[0.04]">
                @foreach($subProg as $row)
                <tr class="hover:bg-blue-50/50/50">
                    <td class="px-4 py-3 font-medium text-black/80 text-xs">{{ $row['subject'] }}</td>
                    @foreach(['Form 1','Form 2','Form 3','Form 4'] as $form)
                    @php
                        $sc2 = $row['forms'][$form] ?? null;
                        $dot = $sc2 === null ? '⬜' : ($sc2 >= 75 ? '🟢' : ($sc2 >= 55 ? '🟡' : ($sc2 >= 45 ? '🟠' : '🔴')));
                    @endphp
                    <td class="px-3 py-3 text-center">
                        @if($sc2 !== null)
                        <span title="{{ $sc2 }}%">{{ $dot }}</span>
                        @else
                        <span class="text-black/20 text-xs">—</span>
                        @endif
                    </td>
                    @endforeach
                    <td class="px-3 py-3 text-center text-xs font-semibold {{ $row['trend'] === 'improving' ? 'text-blue-500' : ($row['trend'] === 'declining' ? 'text-rose-500' : 'text-black/35') }}">
                        {{ $row['trend'] === 'improving' ? '↑' : ($row['trend'] === 'declining' ? '↓' : '→') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 6. SUBJECT RANKING + WEAK SUBJECTS                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

    {{-- Strongest --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
        <h2 class="font-bold text-black/80 mb-4">Your Strongest Subjects</h2>
        @php
            $medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
            $ranked = collect($subProg)->sortByDesc(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? 0)->take(5)->values();
        @endphp
        <div class="space-y-2.5">
            @forelse($ranked as $ri => $row)
            @php $sc3 = $row['forms']['Form 4'] ?? $row['forms']['Form 3'] ?? null; @endphp
            <div class="flex items-center gap-3">
                <span class="text-xl">{{ $medals[$ri] }}</span>
                <span class="flex-1 text-sm font-semibold text-black/75">{{ $row['subject'] }}</span>
                @if($sc3 !== null)
                <span class="text-sm font-bold {{ $sc3 >= 75 ? 'text-blue-600' : 'text-amber-600' }}">{{ $sc3 }}%</span>
                @endif
            </div>
            @empty
            <p class="text-sm text-black/40">No data yet</p>
            @endforelse
        </div>
    </div>

    {{-- Needs Improvement --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
        <h2 class="font-bold text-black/80 mb-4">Needs Improvement</h2>
        @php
            // First: show genuinely weak subjects (below 65%)
            $weakItems = collect($subProg)->filter(function($r) {
                $latest = $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? $r['forms']['Form 2'] ?? null;
                return $latest !== null && $latest < 65;
            })->sortBy(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? 100)->take(5)->values()
              ->map(fn($r) => ['subject' => $r['subject'], 'trend' => $r['trend'] ?? 'Stable', 'change' => 0, 'status' => 'Needs Attention']);
            // Fallback: show declining subjects if no truly weak ones
            if ($weakItems->isEmpty()) {
                $weakItems = collect($trendAnal)->filter(fn($t) => $t['change'] < -3)->sortBy('change')->take(3)->values();
            }
        @endphp
        <div class="space-y-3">
            @forelse($weakItems as $w)
            @php
                $wsc = collect($subProg)->firstWhere('subject', $w['subject']);
                $wval = $wsc ? ($wsc['forms']['Form 4'] ?? $wsc['forms']['Form 3'] ?? null) : null;
            @endphp
            <div class="flex items-start gap-3 p-3 rounded-xl bg-rose-50/60 border border-rose-100">
                <div class="w-2 h-2 rounded-full bg-rose-400 mt-1.5 shrink-0"></div>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-black/80">{{ $w['subject'] }}</p>
                    <p class="text-xs text-rose-600 font-medium">{{ $w['status'] }}</p>
                </div>
                @if($wval !== null)
                <span class="text-xs font-bold text-rose-500">{{ $wval }}%</span>
                @endif
            </div>
            @empty
            <div class="flex items-center gap-2 text-sm text-blue-600 font-semibold">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                All subjects are performing well!
            </div>
            @endforelse
        </div>
    </div>
</div>


{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 8. PERFORMANCE BADGES                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($badges) || !empty($milestones))
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h2 class="font-bold text-black/80 mb-4">Achievements</h2>
    @if(!empty($badges))
    <div class="flex flex-wrap gap-2 mb-4">
        @foreach($badges as $badge)
        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-amber-50 border border-amber-200">
            <span class="text-base">{{ $badge['icon'] }}</span>
            <span class="text-xs font-bold text-amber-800">{{ $badge['label'] }}</span>
        </div>
        @endforeach
    </div>
    @endif
    @if(!empty($milestones))
    <div class="space-y-1.5">
        @foreach(array_slice($milestones, 0, 4) as $m)
        <p class="text-sm text-black/60 flex items-start gap-2">
            <span class="text-blue-500 mt-0.5">✓</span> {{ $m }}
        </p>
        @endforeach
    </div>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 9. RISK MONITOR                                                             --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5 flex items-start gap-4">
    @php
        $rc = ['Low'=>'blue','Medium'=>'amber','High'=>'rose'][$riskLevel];
    @endphp
    <div class="w-16 h-16 rounded-full bg-{{ $rc }}-100 border-2 border-{{ $rc }}-300 flex flex-col items-center justify-center shrink-0">
        <span class="text-[9px] font-bold text-{{ $rc }}-600 uppercase">Risk</span>
        <span class="font-black text-sm text-{{ $rc }}-700">{{ $riskLevel }}</span>
    </div>
    <div>
        <p class="font-bold text-black/80 text-sm mb-1">Academic Risk: {{ $riskLevel }}</p>
        @if(empty($warnings))
        <p class="text-sm text-black/55">No subjects currently threaten your {{ $recPath }} recommendation. Keep up the good work.</p>
        @else
        <ul class="space-y-1">
            @foreach(array_slice($warnings, 0, 3) as $warn)
            <li class="text-sm text-{{ $rc }}-700">• {{ $warn }}</li>
            @endforeach
        </ul>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 10. LEARNING PATTERN                                                        --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@php
    $patterns = [];
    $calcSubs = collect($subProg)->filter(fn($r) => in_array($r['subject'], ['Mathematics','Physics','Chemistry','Accounting']))->pluck('subject');
    $essaySubs = collect($subProg)->filter(fn($r) => in_array($r['subject'], ['English','History','Social Studies','Geography']))->pluck('subject');
    $calcAvg = collect($subProg)->whereIn('subject', $calcSubs->toArray())->avg(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? 0);
    $essayAvg = collect($subProg)->whereIn('subject', $essaySubs->toArray())->avg(fn($r) => $r['forms']['Form 4'] ?? $r['forms']['Form 3'] ?? 0);
    if ($calcSubs->isNotEmpty() && $essaySubs->isNotEmpty()) {
        $patterns[] = $calcAvg > $essayAvg
            ? 'You perform better in calculation-based subjects than essay-based subjects.'
            : 'You perform better in essay and language subjects than calculation subjects.';
    }
    if ($improving >= 2) $patterns[] = 'You improve after every assessment — a strong academic habit.';
    if (!empty($sw['strengths'])) $patterns[] = 'Your strongest area is ' . implode(' and ', array_slice($sw['strengths'], 0, 2)) . '.';
    if ($trendDir === 'up') $patterns[] = 'Your overall performance is on an upward trajectory.';
@endphp
@if(!empty($patterns))
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h2 class="font-bold text-black/80 mb-3">Learning Pattern</h2>
    <p class="text-xs text-black/40 uppercase font-bold mb-3">The system noticed:</p>
    <ul class="space-y-2">
        @foreach($patterns as $pat)
        <li class="flex items-start gap-2 text-sm text-black/70">
            <span class="text-brand-500 font-bold mt-0.5">→</span> {{ $pat }}
        </li>
        @endforeach
    </ul>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 11. GOAL TRACKER                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($rpt['goal_tracking']))
@php $gt = $rpt['goal_tracking']; @endphp
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h2 class="font-bold text-black/80 mb-1">Goal Tracker</h2>
    <p class="text-xs text-black/45 mb-4">Your progress toward your target combination</p>
    @php
        $gtScore = max($gt['readiness_score'] ?? 0, $academicReport['msce_paths'][0]['score'] ?? 0);
        $gtTarget = $gt['target_combination'] ?? ($recPath ? $recPath . ' Combination' : 'Science Combination');
    @endphp
    <div class="flex items-center justify-between mb-2">
        <span class="text-sm font-bold text-black/70">{{ $gtTarget }}</span>
        <span class="text-sm font-bold {{ $gtScore >= 70 ? 'text-blue-600' : 'text-amber-600' }}">
            {{ round($gtScore) }}%
        </span>
    </div>
    <div class="h-3 bg-black/[0.05] rounded-full overflow-hidden mb-2">
        <div class="h-full rounded-full {{ $gtScore >= 70 ? 'bg-blue-500' : 'bg-amber-500' }}"
             style="width:{{ min(100, round($gtScore)) }}%"></div>
    </div>
    @if(!empty($gt['comment']))
    <p class="text-xs text-black/50">{{ $gt['comment'] }}</p>
    @elseif(!empty($gt['improvements_needed']))
    <p class="text-xs text-black/50">Improve: {{ implode(', ', array_slice($gt['improvements_needed'] ?? [], 0, 3)) }}</p>
    @endif
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 12. AI TEACHER COMMENTS                                                    --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($trendAnal))
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h2 class="font-bold text-black/80 mb-1">Teacher Observations</h2>
    <p class="text-xs text-black/40 mb-4">Generated from your grade history</p>
    <div class="space-y-2.5">
        @foreach(array_slice($trendAnal, 0, 5) as $ta)
        @php
            $comment = match(true) {
                $ta['change'] >= 15 => $ta['subject'] . ' has improved dramatically. Outstanding progress!',
                $ta['change'] >= 8  => $ta['subject'] . ' is showing great improvement. Keep it up.',
                $ta['change'] >= 0  => $ta['subject'] . ' is steady. Continue your current approach.',
                $ta['change'] >= -8 => $ta['subject'] . ' needs attention. Review recent topics.',
                default             => $ta['subject'] . ' has declined. Please see your teacher soon.',
            };
        @endphp
        <div class="flex items-start gap-3 p-3 rounded-xl {{ $ta['change'] >= 0 ? 'bg-blue-50 border border-blue-100' : 'bg-amber-50 border border-amber-100' }}">
            <span class="text-sm mt-0.5">{{ $ta['change'] >= 0 ? '✓' : '⚠' }}</span>
            <p class="text-sm text-black/75">{{ $comment }}</p>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 14. AI ACADEMIC REPORT                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <div class="flex items-center gap-2 mb-4">
        <span class="text-xl">📝</span>
        <h2 class="font-bold text-black/80">Academic Report</h2>
    </div>
    @php
        $name  = explode(' ', $user->name)[0];
        $grade = $profile?->form_level ?? 'school';
        $lines = [];
        if ($overallAvg !== null) {
            $lines[] = "{$name} has an overall academic average of {$overallAvg}% — rated as {$perfLevel}.";
        }
        if (!empty($sw['strengths'])) {
            $lines[] = ($trendDir === 'up' ? 'Performance is consistently improving.' : 'Performance is steady.');
            $lines[] = 'Strongest subjects: ' . implode(', ', $sw['strengths']) . '.';
        }
        if (!empty($sw['weaknesses'])) {
            $lines[] = implode(' and ', $sw['weaknesses']) . ' require additional attention.';
        }
        if ($recPath) {
            $lines[] = "Overall, {$name} is academically aligned for the {$recPath} Combination.";
        }
        if (empty($lines)) {
            $lines[] = "Complete your self-assessment and ensure your teacher uploads grades for a full report.";
        }
    @endphp
    <div class="space-y-2">
        @foreach($lines as $line)
        <p class="text-sm text-black/70 leading-relaxed">{{ $line }}</p>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 15. DETAILED GRADES (EXPANDABLE PER SUBJECT)                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
@if(!empty($subProg))
<div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b bg-blue-50/40">
        <h2 class="font-bold text-black/80">Detailed Subject Progress</h2>
        <p class="text-xs text-black/45 mt-0.5">Click a subject to see full grade history</p>
    </div>
    <div class="divide-y divide-black/[0.04]" x-data="{open:null}">
        @foreach($subProg as $ri2 => $row)
        @php
            $latest2 = $row['forms']['Form 4'] ?? $row['forms']['Form 3'] ?? $row['forms']['Form 2'] ?? $row['forms']['Form 1'] ?? null;
            $col2    = $latest2 >= 75 ? 'blue' : ($latest2 >= 55 ? 'amber' : 'rose');
        @endphp
        <div>
            <button @click="open === {{ $ri2 }} ? open = null : open = {{ $ri2 }}"
                    class="w-full flex items-center gap-4 px-5 py-3.5 text-left hover:bg-blue-50/50/50 transition-colors">
                <span class="flex-1 text-sm font-semibold text-black/80">{{ $row['subject'] }}</span>
                @if($latest2 !== null)
                <span class="text-sm font-bold text-{{ $col2 }}-600">{{ $latest2 }}%</span>
                @endif
                <span class="text-xs text-black/30 font-semibold">View Progress →</span>
                <svg class="w-4 h-4 text-black/30 transition-transform" :class="open === {{ $ri2 }} ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open === {{ $ri2 }}" x-transition class="px-5 pb-4">
                <div class="flex items-end gap-4 pt-2">
                    @foreach(['Form 1','Form 2','Form 3','Form 4'] as $form)
                    @php $fsc = $row['forms'][$form] ?? null; @endphp
                    <div class="flex-1 text-center">
                        @if($fsc !== null)
                        <div class="relative mx-auto" style="height:60px;width:40px">
                            <div class="absolute bottom-0 left-0 right-0 rounded-t-md {{ $fsc >= 75 ? 'bg-blue-400' : ($fsc >= 55 ? 'bg-amber-400' : 'bg-rose-400') }}"
                                 style="height:{{ round($fsc * 0.6) }}px"></div>
                        </div>
                        <p class="text-xs font-bold {{ $fsc >= 75 ? 'text-blue-600' : ($fsc >= 55 ? 'text-amber-600' : 'text-rose-500') }} mt-1">{{ $fsc }}%</p>
                        @else
                        <div class="mx-auto bg-slate-100 rounded-t-md" style="height:8px;width:40px"></div>
                        <p class="text-xs text-black/25 mt-1">—</p>
                        @endif
                        <p class="text-[9px] text-black/35 font-bold mt-0.5">{{ str_replace('Form ', 'F', $form) }}</p>
                    </div>
                    @endforeach
                </div>
                @php $ta2 = collect($trendAnal)->firstWhere('subject', $row['subject']); @endphp
                @if($ta2)
                <p class="text-xs text-black/50 mt-3">
                    <span class="font-semibold">Trend:</span> {{ $ta2['trend'] }} · {{ $ta2['status'] }}
                    @if($ta2['change'] != 0) · {{ $ta2['change'] > 0 ? '+' : '' }}{{ $ta2['change'] }}% since Form 1 @endif
                </p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@endif {{-- end $hasData --}}

</div>

@endsection
