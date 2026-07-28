@extends('layouts.dashboard')

@section('title', __('parent.my_children'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">{{ __('parent.guardian') }}</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">{{ __('parent.my_children') }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-bold text-black/80">{{ __('parent.my_children') }}</h1>
        <p class="text-black/50 text-sm mt-1">{{ __('parent.children_page_desc') }}</p>
    </div>

    @forelse($children ?? [] as $child)
    @php
        $topCareer = $child->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
        $avgScore  = $child->academicResults->avg('score');
        $assessed  = $child->assessmentAttempts->where('status','completed')->isNotEmpty();
        $uniElig   = $child->recommendations->where('type','university_program')->where('confidence_score','>=',80)->count();
    @endphp
    <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
        <!-- Child Header -->
        <div class="bg-blue-600 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-900 rounded-xl flex items-center justify-center text-white font-bold text-lg shrink-0">
                    {{ strtoupper(substr($child->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-white text-lg">{{ $child->name }}</h2>
                    <p class="text-white text-xs">
                        {{ $child->studentProfile->form_level ?? '' }}
                        @if($child->studentProfile?->stream)
                            &nbsp;·&nbsp; {{ $child->studentProfile->stream }} {{ __('parent.stream') }}
                        @endif
                    </p>
                </div>
            </div>
            <a href="{{ route('parent.child.detail', $child) }}"
               class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-semibold rounded-xl transition-colors">
                {{ __('parent.full_details') }} →
            </a>
        </div>

        <!-- Child Stats -->
        <div class="p-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-5">
                <div class="text-center">
                    <p class="text-xl font-bold {{ $assessed ? 'text-blue-600' : 'text-blue-600' }}">
                        {{ $assessed ? __('parent.done') : __('parent.pending') }}
                    </p>
                    <p class="text-xs text-black/50 mt-0.5">{{ __('parent.assessment') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-blue-600">{{ $avgScore ? round($avgScore) . '%' : '—' }}</p>
                    <p class="text-xs text-black/50 mt-0.5">{{ __('parent.avg_score') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-blue-600">{{ $topCareer ? $topCareer->recommended->title ?? '—' : '—' }}</p>
                    <p class="text-xs text-black/50 mt-0.5">{{ __('parent.top_career') }}</p>
                </div>
                <div class="text-center">
                    <p class="text-xl font-bold text-blue-600">{{ $uniElig }}</p>
                    <p class="text-xs text-black/50 mt-0.5">{{ __('parent.uni_eligible') }}</p>
                </div>
            </div>

            <!-- Academic Progress Bar -->
            @if($avgScore)
            <div>
                <div class="flex justify-between text-sm mb-1.5">
                    <span class="text-black/50">{{ __('parent.academic_progress') }}</span>
                    <span class="font-semibold {{ $avgScore >= 70 ? 'text-blue-600' : ($avgScore >= 50 ? 'text-blue-600' : 'text-blue-600') }}">
                        {{ round($avgScore) }}%
                    </span>
                </div>
                <div class="h-2.5 bg-black/[0.03] rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all
                        {{ $avgScore >= 70 ? 'bg-blue-600' : ($avgScore >= 50 ? 'bg-blue-500' : 'bg-blue-500') }}"
                         style="width: {{ round($avgScore) }}%"></div>
                </div>
            </div>
            @endif

            <!-- Recent Results -->
            @if($child->academicResults->isNotEmpty())
            <div class="mt-5 pt-4 border-t border-black/10">
                <p class="text-xs font-semibold text-black/50 uppercase tracking-wide mb-3">{{ __('parent.recent_scores') }}</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach($child->academicResults->sortByDesc('term')->take(6) as $result)
                    @php $s = $result->score; $c = $s >= 70 ? 'green' : ($s >= 50 ? 'orange' : 'red'); @endphp
                    <div class="bg-white rounded-xl p-3 text-center">
                        <p class="text-xs text-black/50 truncate">{{ $result->subject->name ?? __('parent.subject') }}</p>
                        <p class="font-bold text-{{ $c }}-600 text-base">{{ $s }}%</p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl p-12 shadow-sm border border-black/10 text-center">
        <div class="w-14 h-14 bg-black/[0.03] rounded-full flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
        </div>
        <h3 class="font-bold text-black/70 mb-1">{{ __('parent.no_children') }}</h3>
        <p class="text-sm text-black/50">{{ __('parent.contact_school') }}</p>
    </div>
    @endforelse
</div>
@endsection
