@extends('layouts.dashboard')

@section('title', 'My Career Path')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">My Career Path</span>
</nav>
@endsection

@section('content')
@php
    $snapshot       = $careerGoal?->progress_snapshot ?? [];
    $matchScore     = $careerGoal?->last_match_score;
    $hasGoal        = $careerGoal && ($careerGoal->career || $careerGoal->universityProgram);
    $subjectRows    = $snapshot['subjects'] ?? $snapshot['subject_details'] ?? [];
    $weakSubs       = $eligibility['weak_subjects'] ?? [];
    $careerTitle    = $careerGoal?->career?->title ?? $careerGoal?->universityProgram?->name ?? null;
    $uniName        = $careerGoal?->universityProgram?->university ?? null;

    // Colour palette for overall readiness score
    $readinessColor = match(true) {
        $matchScore >= 80 => ['text'=>'text-blue-600','bar'=>'bg-blue-500','label'=>'On Track'],
        $matchScore >= 60 => ['text'=>'text-blue-500','bar'=>'bg-blue-400','label'=>'Good Progress'],
        $matchScore >= 40 => ['text'=>'text-amber-600','bar'=>'bg-amber-500','label'=>'Needs Improvement'],
        default           => ['text'=>'text-rose-600','bar'=>'bg-rose-500','label'=>'Needs Work'],
    };

    // Strength subjects (score >= 65)
    $strongSubs = collect($subjectRows)->filter(fn($s) => ($s['score'] ?? 0) >= 65)->values();
    // Improvement subjects (score < 65)
    $improveSubs = collect($subjectRows)->filter(fn($s) => ($s['score'] ?? 0) < 65)->values();

    // Derive a star rating (1-5) from readiness score
    $stars = $matchScore !== null ? max(1, min(5, (int) round($matchScore / 20))) : null;
@endphp

<div class="space-y-6" x-data="careerPathApp()">

    {{-- ── HERO ─────────────────────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-blue-900 p-6 md:p-8 text-white shadow-lg">
        <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-2">Career Goal Tracking</p>
        <h1 class="text-2xl md:text-3xl font-bold">My Career Path</h1>
        <p class="text-blue-100 text-sm mt-2 max-w-2xl">
            Choose a <strong>university</strong>, then a <strong>programme</strong>, and optionally a <strong>career</strong>.
            The system checks your <strong>teacher-uploaded grades</strong> and <strong>assessment</strong> to tell you if you are on track,
            what to improve, and suggests alternatives.
        </p>
    </div>

    {{-- ── SET YOUR GOAL FORM ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6 md:p-8">
        <h2 class="text-lg font-bold text-black/85 mb-1">Set Your Goal</h2>
        <p class="text-sm text-black/50 mb-6">Step 1: Pick university → Step 2: Pick programme → Step 3: Pick career (optional)</p>

        <form action="{{ route('student.career.path.store') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-blue-700 uppercase mb-2">1. Select University</label>
                <select x-model="university" @change="onUniversityChange()"
                        class="w-full rounded-xl border-2 border-blue-100 px-4 py-3 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    <option value="">— Choose a university —</option>
                    @foreach($universities->keys() as $uni)
                    <option value="{{ $uni }}">{{ $uni }}</option>
                    @endforeach
                </select>
            </div>

            <div x-show="university" x-transition>
                <label class="block text-xs font-bold text-blue-700 uppercase mb-2">2. Select Programme</label>
                <select name="university_program_id" x-model="programId" @change="onProgramChange()"
                        class="w-full rounded-xl border-2 border-blue-100 px-4 py-3 text-sm focus:border-blue-500">
                    <option value="">— Choose a programme —</option>
                    <template x-for="p in filteredPrograms" :key="p.id">
                        <option :value="p.id" x-text="p.name + (p.faculty ? ' (' + p.faculty + ')' : '')"></option>
                    </template>
                </select>
                <p class="text-xs text-black/45 mt-1" x-show="filteredPrograms.length === 0">No programmes found for this university.</p>
            </div>

            <div x-show="programId" x-transition>
                <label class="block text-xs font-bold text-blue-700 uppercase mb-2">3. Target Career (optional)</label>
                <select name="career_id" x-model="careerId"
                        class="w-full rounded-xl border-2 border-blue-100 px-4 py-3 text-sm focus:border-blue-500">
                    <option value="">— Select a career —</option>
                    @foreach($careers as $career)
                    <option value="{{ $career->id }}">{{ $career->title }} ({{ $career->category }})</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-bold shadow-md transition-colors cursor-pointer">
                Save Career Path Goal
            </button>
        </form>
    </div>

    {{-- ── RESULTS (only when a goal has been set) ──────────────────────── --}}
    @if($hasGoal)

    {{-- 1. CAREER READINESS DASHBOARD ──────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-blue-800 px-6 py-4">
            <p class="text-blue-100 text-xs font-bold uppercase tracking-widest">Career Readiness Dashboard</p>
            <h2 class="text-white text-xl font-bold mt-1">{{ $careerTitle }}</h2>
            @if($uniName)
            <p class="text-blue-200 text-sm mt-0.5">{{ $uniName }}</p>
            @endif
        </div>

        <div class="p-6 md:p-8">
            @if($matchScore !== null)
            <div class="flex flex-col lg:flex-row lg:items-center gap-8">

                {{-- Big readiness bar --}}
                <div class="flex-1">
                    <div class="flex items-end justify-between mb-2">
                        <span class="text-sm font-bold text-black/70">Overall Readiness</span>
                    </div>
                    <div class="h-5 bg-black/8 rounded-full overflow-hidden">
                        <div class="h-full {{ $readinessColor['bar'] }} rounded-full transition-all duration-700"
                             style="width: {{ min(100, round($matchScore)) }}%"></div>
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <span class="inline-flex items-center gap-1 text-xs font-bold {{ $readinessColor['text'] }}">
                            <span class="w-2 h-2 rounded-full {{ $readinessColor['bar'] }} inline-block"></span>
                            {{ $readinessColor['label'] }}
                        </span>
                        <span class="text-xs text-black/40">Target: 80%+</span>
                    </div>

                    {{-- Score component bars --}}
                    @if(isset($snapshot['performance']))
                    <div class="mt-5 space-y-3">
                        @php
                            $components = [
                                ['label'=>'Grade Performance',  'value'=>$snapshot['performance'] ?? 0,       'color'=>'bg-blue-500',   'w'=>'45%'],
                                ['label'=>'Subject Coverage',   'value'=>$snapshot['subject_coverage'] ?? 0,  'color'=>'bg-blue-500','w'=>'35%'],
                                ['label'=>'Interest Match',     'value'=>$snapshot['interest'] ?? 0,          'color'=>'bg-blue-400',   'w'=>'20%'],
                            ];
                        @endphp
                        @foreach($components as $c)
                        <div>
                            <div class="flex justify-between text-xs font-semibold text-black/55 mb-1">
                                <span>{{ $c['label'] }}</span>
                                <span>{{ round($c['value']) }}% <span class="text-black/30 font-normal">(weight {{ $c['w'] }})</span></span>
                            </div>
                            <div class="h-2 bg-black/8 rounded-full overflow-hidden">
                                <div class="h-full {{ $c['color'] }} rounded-full" style="width: {{ min(100, $c['value']) }}%"></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- Status summary (ring badge removed per user request) --}}
                <div class="shrink-0 min-w-[160px] flex flex-col gap-3">
                    <div class="rounded-xl p-4 text-center {{ $readinessColor['bar'] === 'bg-blue-500' || $readinessColor['bar'] === 'bg-blue-500' ? 'bg-blue-50 border border-blue-200' : 'bg-amber-50 border border-amber-200' }}">
                        <p class="text-[10px] font-bold uppercase text-black/40 mb-1">Status</p>
                        <p class="font-black text-lg {{ $readinessColor['text'] }}">{{ $readinessColor['label'] }}</p>
                        <p class="text-xs text-black/50 mt-0.5">Target: 80%+</p>
                    </div>
                </div>
            </div>
            @else
            <p class="text-black/50 text-sm text-center py-4">No readiness data available yet. Ask your teacher to upload grades.</p>
            @endif
        </div>
    </div>

    {{-- 2. ADMISSION REQUIREMENTS TABLE ────────────────────────────────── --}}
    @if(!empty($subjectRows))
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-blue-700 uppercase mb-4">Admission Requirements Check</h3>
        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[400px]">
                <thead>
                    <tr class="border-b-2 border-blue-100">
                        <th class="text-left py-2 px-3 text-xs font-bold text-black/50 uppercase">Subject</th>
                        <th class="text-center py-2 px-3 text-xs font-bold text-black/50 uppercase">Your Score</th>
                        <th class="text-center py-2 px-3 text-xs font-bold text-black/50 uppercase">Required</th>
                        <th class="text-center py-2 px-3 text-xs font-bold text-black/50 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($subjectRows as $row)
                    @php
                        $score   = $row['score'] ?? null;
                        $met     = $row['met'] ?? ($score !== null && $score >= 60);
                        $reqMin  = $row['required'] ?? 60;
                        $emoji   = match(true) {
                            $score === null     => '❔',
                            $score >= 75        => '🟢',
                            $score >= 60        => '🟡',
                            default             => '🔴',
                        };
                        $statusLabel = match(true) {
                            $score === null     => 'No Data',
                            $score >= 75        => 'Excellent',
                            $score >= 60        => 'Met',
                            default             => 'Below Required',
                        };
                        $statusClass = $met ? 'text-blue-700 bg-blue-50' : 'text-rose-700 bg-rose-50';
                    @endphp
                    <tr class="hover:bg-blue-50/40 transition">
                        <td class="py-2.5 px-3 font-medium text-black/80">{{ $row['subject'] ?? 'Subject' }}</td>
                        <td class="py-2.5 px-3 text-center">
                            @if($score !== null)
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-20 h-1.5 bg-black/10 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $met ? 'bg-blue-500' : 'bg-amber-400' }}"
                                         style="width: {{ min(100, $score) }}%"></div>
                                </div>
                                <span class="font-bold {{ $met ? 'text-blue-700' : 'text-amber-600' }} text-xs w-10">{{ round($score) }}%</span>
                            </div>
                            @else
                            <span class="text-black/30 text-xs">—</span>
                            @endif
                        </td>
                        <td class="py-2.5 px-3 text-center text-xs text-black/50 font-semibold">{{ $reqMin }}%+</td>
                        <td class="py-2.5 px-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $statusClass }}">
                                {{ $emoji }} {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 3. SUBJECT PERFORMANCE (visual) ─────────────────────────────────── --}}
    @if(!empty($subjectRows))
    <div class="grid md:grid-cols-2 gap-6">

        @if($strongSubs->isNotEmpty())
        <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">💪</span>
                <h3 class="text-sm font-bold text-blue-700 uppercase">Your Strengths</h3>
            </div>
            <div class="space-y-3">
                @foreach($strongSubs->take(5) as $s)
                @php $sc2 = $s['score'] ?? 0; @endphp
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-black/70">{{ $s['subject'] ?? 'Subject' }}</span>
                        <span class="text-blue-600">{{ round($sc2) }}% 🟢</span>
                    </div>
                    <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $sc2) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($improveSubs->isNotEmpty())
        <div class="bg-white rounded-2xl border-2 border-amber-100 shadow-sm p-6">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">📈</span>
                <h3 class="text-sm font-bold text-amber-700 uppercase">Areas for Improvement</h3>
            </div>
            <div class="space-y-3">
                @foreach($improveSubs->take(5) as $s)
                @php
                    $sc2 = $s['score'] ?? 0;
                    $target = max(65, $sc2 + 10);
                    $isCritical = $sc2 < 50;
                @endphp
                <div>
                    <div class="flex justify-between text-xs font-semibold mb-1">
                        <span class="text-black/70">{{ $s['subject'] ?? 'Subject' }}</span>
                        <span class="{{ $isCritical ? 'text-rose-600' : 'text-amber-600' }}">
                            {{ round($sc2) }}% {{ $isCritical ? '🔴' : '🟡' }} → Target {{ $target }}%
                        </span>
                    </div>
                    <div class="h-2 bg-black/8 rounded-full overflow-hidden relative">
                        <div class="h-full {{ $isCritical ? 'bg-rose-400' : 'bg-amber-400' }} rounded-full" style="width: {{ min(100, $sc2) }}%"></div>
                        <div class="absolute top-0 bottom-0 w-0.5 bg-black/20" style="left: 65%"></div>
                    </div>
                    <p class="text-[10px] text-black/35 mt-0.5">Minimum recommended: 65%</p>
                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
    @endif

    {{-- 4. UNIVERSITY ADMISSION READINESS ───────────────────────────────── --}}
    @php
        $uniList = $universities->keys()->take(4)->values()->all();

        // Calculate real readiness per university based on student's actual grades
        $studentGradeMap = collect($subjectRows)->keyBy('subject');
        $studentScores   = collect($subjectRows)->map(fn($s) => $s['score'] ?? 0);
        $studentAvg      = $studentScores->isNotEmpty() ? round($studentScores->avg(), 1) : 0;
        $subjectsMet     = collect($subjectRows)->filter(fn($s) => ($s['score'] ?? 0) >= 60)->count();
        $totalSubjCount  = max(1, count($subjectRows));
        $meetRate        = round(($subjectsMet / $totalSubjCount) * 100);

        // Each university has a required average threshold based on competitiveness
        $uniThresholds = [
            'kamuzu'    => 82, 'kuhe'    => 82,
            'unima'     => 78, 'chancellor' => 78,
            'mzuzu'     => 68, 'mzu'    => 68,
            'mubas'     => 65, 'polytechnic' => 65,
            'luanar'    => 62,
            'default'   => 65,
        ];

        $uniScores = [];
        foreach($uniList as $u) {
            $threshold = $uniThresholds['default'];
            $uLower = strtolower($u);
            foreach($uniThresholds as $kw => $req) {
                if($kw !== 'default' && str_contains($uLower, $kw)) { $threshold = $req; break; }
            }

            // Calculate readiness: blend grade average vs requirement + subject meet rate
            if ($studentAvg >= $threshold) {
                $gradeReadiness = min(95, round(75 + (($studentAvg - $threshold) / max(1, 100 - $threshold)) * 20));
            } else {
                $gradeReadiness = max(10, round(($studentAvg / $threshold) * 70));
            }
            $uniReadiness = round($gradeReadiness * 0.65 + $meetRate * 0.35);
            $uniReadiness = min(95, max(8, $uniReadiness));

            $uniScores[] = [
                'name'      => $u,
                'score'     => $uniReadiness,
                'threshold' => $threshold,
                'current'   => $u === ($uniName ?? ''),
            ];
        }
        // Sort: selected university first, then by score
        usort($uniScores, fn($a, $b) => ($b['current'] <=> $a['current']) ?: ($b['score'] <=> $a['score']));
    @endphp
    @if(!empty($uniList))
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-blue-700 uppercase mb-4">University Admission Readiness</h3>
        <p class="text-xs text-black/45 mb-4">Based on your teacher-uploaded grades ({{ $studentAvg }}% average) matched against each university's typical entry requirements.</p>
        <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($uniScores as $us)
            @php
                $uColor = match(true) {
                    $us['score'] >= 80 => ['bg'=>'bg-blue-500','text'=>'text-blue-700','border'=>'border-2 border-blue-200 bg-blue-50'],
                    $us['score'] >= 60 => ['bg'=>'bg-blue-400','text'=>'text-blue-600','border'=>'border border-blue-100 bg-blue-50/60'],
                    $us['score'] >= 40 => ['bg'=>'bg-amber-500','text'=>'text-amber-700','border'=>'border border-amber-200 bg-amber-50'],
                    default            => ['bg'=>'bg-rose-400','text'=>'text-rose-700','border'=>'border border-rose-200 bg-rose-50'],
                };
                $eligLabel = $us['score'] >= 80 ? 'Strong Candidate' : ($us['score'] >= 60 ? 'Eligible' : ($us['score'] >= 40 ? 'Borderline — Improve' : 'Needs Work'));
            @endphp
            <div class="rounded-xl {{ $us['current'] ? 'border-2 border-blue-500 bg-blue-50' : $uColor['border'] }} p-4 text-center">
                @if($us['current'])
                <span class="inline-block px-2 py-0.5 bg-blue-600 text-white text-[10px] font-bold rounded-full mb-2">Your Selected University</span>
                @endif
                <p class="text-xs font-bold text-black/70 mb-2 leading-tight">{{ Str::limit($us['name'], 28) }}</p>
                <p class="text-2xl font-black {{ $uColor['text'] }}">{{ $us['score'] }}%</p>
                <div class="mt-2 h-1.5 bg-black/10 rounded-full overflow-hidden">
                    <div class="h-full {{ $uColor['bg'] }} rounded-full" style="width: {{ $us['score'] }}%"></div>
                </div>
                <p class="text-[10px] mt-1 font-semibold {{ $uColor['text'] }}">{{ $eligLabel }}</p>
                <p class="text-[9px] text-black/35 mt-0.5">Requires ~{{ $us['threshold'] }}% avg</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 5. RELATED DEGREE PROGRAMMES ────────────────────────────────────── --}}
    @php
        $relatedPrograms = [];
        foreach($universities as $u => $uProgs) {
            foreach($uProgs as $prog) {
                if($matchScore !== null) {
                    $reqSubs = $prog->requiredSubjects ?? collect();
                    if ($reqSubs->isEmpty()) {
                        // No specific requirements: estimate from student average
                        $pScore = min(90, max(10, round($studentAvg * 0.85)));
                    } else {
                        $metCnt = 0;
                        foreach ($reqSubs as $rs) {
                            $minG = $rs->pivot->minimum_grade ?? 60;
                            $sScore = $studentGradeMap[$rs->name]['score'] ?? null;
                            if ($sScore === null) {
                                // try case-insensitive lookup
                                foreach ($studentGradeMap as $sn => $sd) {
                                    if (strcasecmp($sn, $rs->name) === 0) { $sScore = $sd['score'] ?? null; break; }
                                }
                            }
                            if ($sScore !== null && $sScore >= $minG) $metCnt++;
                        }
                        $reqTotal = max(1, $reqSubs->count());
                        $pScore = round(($metCnt / $reqTotal) * 70 + $studentAvg * 0.3 * 0.85);
                    }
                    $pScore = min(95, max(8, $pScore));
                    $relatedPrograms[] = ['name' => $prog->name, 'university' => $u, 'score' => $pScore, 'stars' => max(1, min(5, (int) round($pScore / 20)))];
                }
            }
        }
        usort($relatedPrograms, fn($a, $b) => $b['score'] - $a['score']);
        $relatedPrograms = array_slice($relatedPrograms, 0, 6);
    @endphp
    @if(!empty($relatedPrograms))
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-blue-700 uppercase mb-4">Related Degree Programmes</h3>
        <div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($relatedPrograms as $rp)
            <div class="rounded-xl border border-blue-100 p-4 hover:border-blue-300 hover:shadow-sm transition">
                <p class="font-semibold text-black/85 text-sm leading-snug">{{ $rp['name'] }}</p>
                <p class="text-xs text-black/40 mt-0.5">{{ Str::limit($rp['university'], 30) }}</p>
                <div class="flex items-center mt-3">
                    <div class="flex gap-0.5">
                        @for($i = 1; $i <= 5; $i++)
                        <span class="text-base {{ $i <= $rp['stars'] ? 'text-amber-400' : 'text-black/15' }}">★</span>
                        @endfor
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- 6. ALTERNATIVE CAREER PATHS ─────────────────────────────────────── --}}
    @if(!empty($alternativeCareers))
    <div class="bg-white rounded-2xl border-2 border-blue-100 shadow-sm p-6">
        <h3 class="text-sm font-bold text-blue-700 uppercase mb-4">Alternative Career Paths</h3>
        <div class="overflow-x-auto -mx-2">
            <table class="w-full text-sm min-w-[500px]">
                <thead>
                    <tr class="border-b-2 border-blue-100">
                        <th class="text-left py-2 px-3 text-xs font-bold text-black/50 uppercase">Career</th>
                        <th class="text-center py-2 px-3 text-xs font-bold text-black/50 uppercase">Match</th>
                        <th class="text-left py-2 px-3 text-xs font-bold text-black/50 uppercase">Why Suitable?</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($alternativeCareers as $alt)
                    @php
                        $altScore = round($alt['score'] ?? 0);
                        $altColor = $altScore >= 75 ? 'text-blue-600 bg-blue-50' : ($altScore >= 55 ? 'text-blue-600 bg-blue-50' : 'text-amber-600 bg-amber-50');
                    @endphp
                    <tr class="hover:bg-blue-50/40">
                        <td class="py-3 px-3 font-semibold text-black/80">{{ $alt['career']->title ?? 'Career' }}</td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $altColor }}">
                                {{ $altScore }}%
                            </span>
                        </td>
                        <td class="py-3 px-3 text-black/60 text-xs leading-relaxed">{{ Str::limit($alt['reason'] ?? '', 120) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- 8. PERSONALISED ADVICE ───────────────────────────────────────────── --}}
    <div class="rounded-2xl bg-gradient-to-r from-blue-600 to-blue-800 p-6 md:p-8 text-white shadow-lg">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center text-2xl">💡</div>
            <div>
                <h3 class="font-bold text-lg mb-2">Personalised Advice</h3>
                @if($matchScore !== null)
                    @if($matchScore >= 80)
                    <p class="text-blue-100 leading-relaxed text-sm">
                        You are in <strong class="text-white">excellent shape</strong> for your {{ $careerTitle }} goal!
                        Your grades show strong readiness across key subjects.
                        Keep up the momentum, attend counselling sessions to refine your university application strategy,
                        and maintain your grades in your final terms.
                    </p>
                    @elseif($matchScore >= 60)
                    <p class="text-blue-100 leading-relaxed text-sm">
                        You are making <strong class="text-white">good progress</strong> toward {{ $careerTitle }}.
                        @if(!empty($weakSubs)) Focus on improving <strong class="text-white">{{ implode(', ', array_slice($weakSubs, 0, 2)) }}</strong> to push your readiness above 80%. @endif
                        Book a counselling session to plan your Form 4 subject selection and university application timeline.
                    </p>
                    @elseif($matchScore >= 40)
                    <p class="text-blue-100 leading-relaxed text-sm">
                        Your {{ $careerTitle }} goal is <strong class="text-white">achievable but needs focused effort</strong>.
                        @if(!empty($weakSubs)) Your weakest areas are <strong class="text-white">{{ implode(', ', array_slice($weakSubs, 0, 3)) }}</strong> — dedicate extra study time to these subjects. @endif
                        Consider speaking to a counsellor about alternative pathways and support resources available at school.
                    </p>
                    @else
                    <p class="text-blue-100 leading-relaxed text-sm">
                        Your current grades suggest you may need <strong class="text-white">significant improvement</strong> to reach {{ $careerTitle }}.
                        This does not mean it is impossible — speak with your counsellor about a structured improvement plan,
                        consider alternative related careers that may be a better fit right now,
                        and dedicate consistent study time each week.
                    </p>
                    @endif
                @else
                <p class="text-blue-100 text-sm">
                    Ask your class teacher to upload your grades so the system can generate a personalised readiness analysis
                    and advice for your {{ $careerTitle ?? 'chosen career' }} goal.
                </p>
                @endif

                @if(!empty($weakSubs))
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach(array_slice($weakSubs, 0, 4) as $ws)
                    <span class="px-3 py-1 bg-white/15 rounded-full text-xs font-bold">📌 Improve {{ $ws }}</span>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    @else
    {{-- Empty state --}}
    <div class="bg-blue-50 border-2 border-dashed border-blue-200 rounded-2xl p-10 text-center">
        <p class="text-4xl mb-3">🎯</p>
        <p class="font-semibold text-black/60">Select a university and programme above to start tracking your career path.</p>
        <p class="text-sm text-black/40 mt-1">Your personalised readiness dashboard will appear here.</p>
    </div>
    @endif

</div>

@push('scripts')
<script>
function careerPathApp() {
    const allPrograms = @json($programsForJs);

    return {
        careerId: '{{ $careerGoal?->career_id ?? '' }}',
        university: @json($careerGoal?->universityProgram?->university ?? ''),
        programId: '{{ $careerGoal?->university_program_id ?? '' }}',
        filteredPrograms: [],
        onUniversityChange() {
            this.programId = '';
            this.filteredPrograms = this.university
                ? allPrograms.filter(p => p.university === this.university)
                : [];
        },
        onProgramChange() {
            const prog = allPrograms.find(p => p.id == this.programId);
            if (prog && prog.career_ids && prog.career_ids.length === 1) {
                this.careerId = String(prog.career_ids[0]);
            }
        },
        init() {
            if (this.university) this.onUniversityChange();
        }
    };
}

function whatIfSim(subjects, baseScore) {
    return {
        subjects: subjects,
        baseScore: baseScore,
        selectedSub: '',
        simScore: 0,
        simReadiness: baseScore,
        recalc() {
            if (!this.selectedSub) { this.simReadiness = this.baseScore; return; }
            const orig = this.subjects.find(s => s.subject === this.selectedSub);
            if (!orig) return;
            const origScore = parseFloat(orig.score ?? 0);
            const delta = this.simScore - origScore;
            // Weight each subject equally for estimate
            const weight = this.subjects.length > 0 ? 0.45 / this.subjects.length : 0;
            const improvement = delta * weight;
            this.simReadiness = Math.round(Math.min(100, Math.max(0, this.baseScore + improvement)));
        },
        init() {
            if (this.subjects.length > 0) {
                this.selectedSub = this.subjects[0]?.subject ?? '';
                this.simScore = parseFloat(this.subjects[0]?.score ?? 0);
            }
        }
    };
}
</script>
@endpush
@endsection
