@if(isset($report) && !empty($report))
    @if(!empty($report['overview']))
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Performance Level</p>
            <p class="text-xl font-black">{{ $report['overview']['performance_level'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Trend</p>
            <p class="text-xl font-black">{{ $report['overview']['trend'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Recommended Path</p>
            <p class="text-lg font-black text-brand-600">{{ str_replace(' Path', '', $report['overview']['recommended_path']) }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-blue-100 p-5 shadow-sm">
            <p class="text-xs font-bold text-blue-600 uppercase mb-1">Academic Health</p>
            <p class="text-xl font-black">{{ $report['academic_health']['level'] ?? 'N/A' }}</p>
        </div>
    </div>
    @endif

    @if(!empty($report['subject_progress']))
    <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b bg-blue-50/50">
            <h2 class="font-bold">Subject Progress (Form 1-4)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr class="bg-blue-50 text-left text-xs uppercase">
                        <th class="px-4 py-3">Subject</th>
                        @foreach(['Form 1', 'Form 2', 'Form 3', 'Form 4'] as $f)
                            <th class="px-4 py-3">{{ $f }}</th>
                        @endforeach
                        <th class="px-4 py-3">Trend</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report['subject_progress'] as $row)
                    <tr class="border-t">
                        <td class="px-4 py-3 font-medium">{{ $row['subject'] }}</td>
                        @foreach(['Form 1', 'Form 2', 'Form 3', 'Form 4'] as $f)
                        <td class="px-4 py-3">
                            @if($row['forms'][$f] ?? null)
                                {{ $row['forms'][$f] }}%
                            @else
                                -
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-3">
                            @if($row['trend'] === 'improving')
                                Improving
                            @elseif($row['trend'] === 'declining')
                                Declining
                            @else
                                Stable
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="grid md:grid-cols-2 gap-6">
        @if(!empty($report['strengths_weaknesses']))
        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold text-emerald-800 mb-2">Strong Subjects</h3>
            @forelse($report['strengths_weaknesses']['strengths'] as $s)
                <p class="text-sm text-emerald-700">{{ $s }}</p>
            @empty
                <p class="text-sm text-black/40">None listed yet</p>
            @endforelse
            <h3 class="font-bold text-rose-800 mt-4 mb-2">Needs Improvement</h3>
            @forelse($report['strengths_weaknesses']['weaknesses'] as $w)
                <p class="text-sm text-rose-700">{{ $w }}</p>
            @empty
                <p class="text-sm text-black/40">None listed yet</p>
            @endforelse
        </div>
        @endif

        @if(!empty($report['combination_readiness']))
        <div class="bg-white rounded-2xl border p-6">
            <h3 class="font-bold mb-3">Combination Readiness</h3>
            @foreach($report['combination_readiness'] as $cr)
            <div class="flex justify-between py-2 border-b last:border-0">
                <span class="text-sm">{{ $cr['combination'] }}</span>
                <span class="text-xs font-bold px-2 py-1 rounded-lg bg-blue-100">{{ $cr['status'] }}</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    @if(!empty($report['early_warnings']))
        @foreach($report['early_warnings'] as $warn)
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-sm text-rose-800">
            <strong>Alert:</strong> {{ $warn['message'] }}
        </div>
        @endforeach
    @endif

    @if(!empty($report['improvement_advice']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-2">Improvement Advice</h3>
        <ul class="space-y-1">
            @foreach($report['improvement_advice'] as $tip)
                <li class="text-sm">{{ $tip }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($report['combination_comparison']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-3">Combination Comparison</h3>
        @foreach($report['combination_comparison'] as $cc)
        <div class="flex justify-between py-2 border-b">
            <span class="text-sm">{{ $cc['combination'] }} ({{ $cc['score'] }}%)</span>
            <span class="text-xs font-bold">{{ $cc['status'] }}</span>
        </div>
        @endforeach
    </div>
    @endif

    @if(!empty($report['predictions']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-3">Predicted Next-Term Scores</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach(array_slice($report['predictions'], 0, 4) as $pred)
            <div class="text-center p-3 bg-blue-50 rounded-xl">
                <p class="text-xs">{{ $pred['subject'] }}</p>
                <p class="font-bold text-brand-600">{{ $pred['range'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($report['improvement_roadmap']['steps']))
    <div class="bg-brand-50 rounded-2xl border border-brand-100 p-6">
        <h3 class="font-bold text-brand-900">Roadmap - {{ $report['improvement_roadmap']['goal'] }}</h3>
        <ul class="mt-2 space-y-1">
            @foreach($report['improvement_roadmap']['steps'] as $step)
                <li class="text-sm">{{ $step }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if(!empty($report['goal_tracking']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-2">Goal Tracking — {{ str_replace(' Path', '', $report['goal_tracking']['target'] ?? '') }}</h3>
        <p class="text-sm text-emerald-700 mb-1">On track: {{ implode(', ', $report['goal_tracking']['met'] ?? []) ?: 'None yet' }}</p>
        <p class="text-sm text-rose-700">Needs improvement: {{ implode(', ', $report['goal_tracking']['needs_improvement'] ?? []) ?: 'None' }}</p>
    </div>
    @endif

    @if(!empty($report['weak_subject_impact']))
    <div class="bg-amber-50 rounded-2xl border border-amber-200 p-6">
        <h3 class="font-bold text-amber-900 mb-2">Subjects Holding You Back</h3>
        @foreach($report['weak_subject_impact']['holding_back'] ?? [] as $item)
            <p class="text-sm text-amber-800">• {{ $item['subject'] }} ({{ $item['score'] }}%)</p>
        @endforeach
    </div>
    @endif

    @if(!empty($report['recommendation_stability']['history']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-2">Recommendation Stability</h3>
        <p class="text-sm text-black/60 mb-2">Trend: {{ $report['recommendation_stability']['trend'] ?? 'Tracking' }}</p>
        @foreach($report['recommendation_stability']['history'] as $h)
            <p class="text-sm">{{ $h['period'] ?? '' }}: {{ $h['combination'] ?? '' }}</p>
        @endforeach
    </div>
    @endif

    @if(!empty($report['peer_comparison']))
    <div class="bg-white rounded-2xl border p-6">
        <h3 class="font-bold mb-3">Peer Comparison (Anonymous)</h3>
        @foreach($report['peer_comparison'] as $pc)
        <div class="flex justify-between text-sm py-1 border-b">
            <span>{{ $pc['subject'] }} — peers typically {{ $pc['peer_typical'] }}</span>
            <span class="font-bold {{ ($pc['above_peer'] ?? false) ? 'text-emerald-600' : 'text-rose-600' }}">You: {{ $pc['your_score'] }}%</span>
        </div>
        @endforeach
    </div>
    @endif
@endif
