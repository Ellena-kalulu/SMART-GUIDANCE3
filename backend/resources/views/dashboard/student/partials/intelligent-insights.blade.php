@php
    $confidenceStyles = [
        'very_high' => ['bg' => 'bg-emerald-100', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'bar' => 'bg-emerald-500', 'ring' => 'ring-emerald-500/20'],
        'high'      => ['bg' => 'bg-blue-100',    'text' => 'text-blue-700',    'border' => 'border-blue-200',    'bar' => 'bg-blue-500',    'ring' => 'ring-blue-500/20'],
        'moderate'  => ['bg' => 'bg-amber-100',   'text' => 'text-amber-700',   'border' => 'border-amber-200',   'bar' => 'bg-amber-500',   'ring' => 'ring-amber-500/20'],
        'low'       => ['bg' => 'bg-gray-100',    'text' => 'text-gray-600',    'border' => 'border-gray-200',    'bar' => 'bg-gray-400',    'ring' => 'ring-gray-400/20'],
    ];
    $confLevel = $analysis?->confidence_level ?? 'moderate';
    $confStyle = $confidenceStyles[$confLevel] ?? $confidenceStyles['moderate'];
@endphp

@if($analysis)
<section class="space-y-5">
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center shadow-md shadow-blue-600/25">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-lg font-bold text-black/90">Intelligent Insights</h2>
                <p class="text-xs text-black/45">Powered by your assessment and teacher-uploaded grades</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border {{ $confStyle['bg'] }} {{ $confStyle['text'] }} {{ $confStyle['border'] }} ring-2 {{ $confStyle['ring'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $confStyle['bar'] }}"></span>
                {{ $analysis->confidenceLabel() }} Confidence
            </span>
            <span class="text-sm font-black text-black/70">{{ number_format($analysis->confidence_score, 0) }}%</span>
        </div>
    </div>

    @if(!empty($analysis->subject_conflicts))
    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
        <div class="flex items-start gap-3">
            <div class="w-9 h-9 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
            </div>
            <div class="flex-1 space-y-3">
                <p class="font-semibold text-amber-800 text-sm">Subject Combination Warning</p>
                @foreach($analysis->subject_conflicts as $conflict)
                <div class="text-sm text-amber-700/90 space-y-1.5">
                    <p>{{ $conflict['warning'] ?? 'Your current subjects may not align with your recommended pathway.' }}</p>
                    @if(!empty($conflict['selected_subjects']))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($conflict['selected_subjects'] as $sub)
                        <span class="px-2 py-0.5 bg-amber-100/80 text-amber-800 text-xs rounded-md font-medium">{{ $sub }}</span>
                        @endforeach
                    </div>
                    @endif
                    @if(!empty($conflict['suggested']))
                    <p class="text-xs font-medium text-amber-800">
                        Suggested: <span class="font-bold">{{ $conflict['suggested'] }}</span>
                        @if(!empty($conflict['suggested_score']))
                        ({{ number_format($conflict['suggested_score'], 0) }}% suitability)
                        @endif
                    </p>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
        <div class="xl:col-span-2 bg-white rounded-2xl border border-black/8 shadow-sm p-6">
            <h3 class="text-sm font-bold text-black/80 uppercase tracking-wider mb-4">Subject Suitability Scores</h3>
            @php $groups = $analysis->subject_suitability_scores['groups'] ?? []; @endphp
            @if(!empty($groups))
            <div class="space-y-4">
                @foreach($groups as $idx => $group)
                @php
                    $score = $group['score'] ?? 0;
                    $barColor = $score >= 80 ? 'bg-emerald-500' : ($score >= 65 ? 'bg-blue-500' : ($score >= 50 ? 'bg-amber-400' : 'bg-gray-400'));
                @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            @if($idx === 0)
                            <span class="px-1.5 py-0.5 bg-brand-100 text-brand-700 text-[10px] font-bold uppercase rounded">Top Pick</span>
                            @endif
                            <span class="text-sm font-semibold text-black/75">{{ $group['label'] ?? 'Combination' }}</span>
                        </div>
                        <span class="text-sm font-bold text-brand-600">{{ number_format($score, 0) }}%</span>
                    </div>
                    <div class="h-2.5 bg-black/[0.04] rounded-full overflow-hidden">
                        <div class="h-full {{ $barColor }} rounded-full" style="width: {{ min(100, $score) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @if($analysis->top_combination)
            <p class="mt-5 pt-4 border-t border-black/5 text-xs text-black/45">
                Recommended combination:
                <span class="font-semibold text-black/70">{{ $analysis->top_combination['combination'] ?? $analysis->top_combination['label'] ?? '—' }}</span>
            </p>
            @endif
            @endif
        </div>

        <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
            <h3 class="text-sm font-bold text-black/80 uppercase tracking-wider mb-4">Interest Analysis</h3>
            @if(!empty($analysis->interest_scores))
            <div class="space-y-3">
                @foreach($analysis->interest_scores as $interest => $score)
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs font-medium text-black/60">{{ $interest }}</span>
                        <span class="text-xs font-bold text-blue-600">{{ $score }}%</span>
                    </div>
                    <div class="h-2 bg-blue-50 rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $score) }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
            <h3 class="text-sm font-bold text-black/80 uppercase tracking-wider mb-4">Academic Profile</h3>
            <div class="grid grid-cols-2 gap-5">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 uppercase mb-2">Strengths</p>
                    @if(!empty($analysis->strengths))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($analysis->strengths as $s)
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 text-xs font-medium rounded-lg">{{ $s }}</span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-black/40 italic">Add academic results to detect strengths.</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs font-semibold text-rose-500 uppercase mb-2">Areas to Improve</p>
                    @if(!empty($analysis->weaknesses))
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($analysis->weaknesses as $w)
                        <span class="px-2.5 py-1 bg-rose-50 text-rose-600 text-xs font-medium rounded-lg">{{ $w }}</span>
                        @endforeach
                    </div>
                    @else
                    <p class="text-xs text-black/40 italic">No significant weaknesses detected.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
            <h3 class="text-sm font-bold text-black/80 uppercase tracking-wider mb-4">Predicted MSCE Grades</h3>
            @if(!empty($analysis->predicted_performance))
            <div class="space-y-2.5 max-h-48 overflow-y-auto">
                @foreach($analysis->predicted_performance as $pred)
                <div class="flex items-center gap-3">
                    <span class="text-xs font-medium text-black/60 w-28 truncate">{{ $pred['subject'] ?? 'Subject' }}</span>
                    <div class="flex-1 h-2 bg-black/[0.04] rounded-full overflow-hidden">
                        <div class="h-full bg-brand-500 rounded-full" style="width: {{ min(100, $pred['score'] ?? 0) }}%"></div>
                    </div>
                    <span class="text-xs font-bold text-black/70 w-8 text-right">{{ $pred['grade'] ?? '—' }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-sm text-black/40 italic">Record academic results to see grade predictions.</p>
            @endif
        </div>
    </div>

    @if($analysis->explanation)
    <div class="rounded-2xl border border-brand-100 bg-gradient-to-r from-brand-50/80 to-blue-50/50 p-6">
        <h3 class="font-bold text-black/80 text-sm mb-1">AI Explanation</h3>
        <p class="text-sm text-black/60 leading-relaxed">{{ $analysis->explanation }}</p>
    </div>
    @endif

    @if(!empty($recommendationHistory) && count($recommendationHistory) > 0)
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
        <h3 class="text-sm font-bold text-black/80 uppercase tracking-wider mb-5">Assessment History</h3>
        <div class="space-y-4">
            @foreach($recommendationHistory as $idx => $entry)
            <div class="flex items-start gap-4">
                <div class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 {{ $idx === count($recommendationHistory) - 1 ? 'bg-brand-600 text-white' : 'bg-gray-100 text-gray-500' }}">
                    <span class="text-xs font-bold">{{ $idx + 1 }}</span>
                </div>
                <div>
                    <div class="flex items-center gap-3">
                        <p class="text-sm font-semibold text-black/80">{{ $entry['assessment'] ?? 'Assessment' }}</p>
                        <span class="text-xs text-black/40">{{ $entry['date'] ?? '' }}</span>
                    </div>
                    <p class="text-sm text-black/55 mt-0.5">
                        Top combination: <span class="font-medium">{{ $entry['combination'] ?? '—' }}</span>
                        <span class="text-brand-600 font-semibold">({{ number_format($entry['score'] ?? 0, 0) }}%)</span>
                    </p>
                    @if(!empty($entry['confidence']))
                    <p class="text-xs text-black/40">Confidence: {{ number_format($entry['confidence'], 0) }}%</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</section>
@endif
