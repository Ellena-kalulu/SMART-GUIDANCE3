@php
    $levelStyles = [
        'high'   => ['border' => 'border-red-200', 'bg' => 'bg-red-50', 'avatar' => 'bg-red-100 text-red-700', 'score' => 'text-red-700'],
        'medium' => ['border' => 'border-amber-200', 'bg' => 'bg-amber-50', 'avatar' => 'bg-amber-100 text-amber-700', 'score' => 'text-amber-700'],
    ];
    $style = $levelStyles[$level] ?? $levelStyles['medium'];
    $flagLevelDot = ['high' => 'bg-red-500', 'medium' => 'bg-amber-500', 'low' => 'bg-gray-400'];
@endphp
<div class="bg-white rounded-2xl border {{ $style['border'] }} shadow-sm p-4">
    <div class="flex items-start justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-10 h-10 rounded-full {{ $style['avatar'] }} flex items-center justify-center font-bold text-xs shrink-0">
                {{ strtoupper(substr($data['student']->name, 0, 2)) }}
            </div>
            <div class="min-w-0">
                <p class="font-semibold text-gray-900 text-sm truncate">{{ $data['student']->name }}</p>
                <p class="text-xs text-gray-500">
                    {{ $data['student']->studentProfile?->form_level ?? 'N/A' }}
                    @if($data['avgScore']) &middot; Avg: {{ $data['avgScore'] }}% @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3 shrink-0">
            <div class="text-right">
                <p class="text-lg font-bold {{ $style['score'] }} leading-none">{{ $data['riskScore'] }}</p>
                <p class="text-[10px] text-gray-400 uppercase">Risk Points</p>
            </div>
            <a href="{{ route('counsellor.student.detail', $data['student']) }}"
               class="text-xs text-blue-600 hover:text-blue-700 font-medium">View</a>
        </div>
    </div>

    @if(!empty($data['riskFlags']))
    <div class="flex flex-wrap gap-2 mt-3">
        @foreach($data['riskFlags'] as $flag)
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg {{ $style['bg'] }} text-xs font-medium text-gray-700" title="{{ $flag['detail'] }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $flagLevelDot[$flag['level']] ?? 'bg-gray-400' }}"></span>
            {{ $flag['label'] }}
        </span>
        @endforeach
    </div>
    @endif
</div>
