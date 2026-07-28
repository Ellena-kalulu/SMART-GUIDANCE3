@props(['title', 'value', 'icon', 'color' => 'blue', 'change' => null])

<div class="bg-white rounded-2xl border border-blue-100 p-5 hover:shadow-md transition-all duration-200">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mb-2">{{ $title }}</p>
            <p class="text-3xl font-bold text-black/85 leading-none">{{ $value }}</p>
            @if($change)
                <p class="text-xs mt-2 font-medium text-blue-600">{{ $change }} vs last month</p>
            @endif
        </div>
        <div class="w-11 h-11 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                {!! $icon !!}
            </svg>
        </div>
    </div>
</div>
