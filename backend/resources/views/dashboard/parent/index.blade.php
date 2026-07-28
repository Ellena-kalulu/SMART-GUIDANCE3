@extends('layouts.dashboard')

@section('title', __('parent.dashboard'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('content')
<div class="space-y-6">

    <div class="bg-gradient-to-br from-blue-700 via-blue-800 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-20" style="background-image: radial-gradient(circle at 80% 20%, #60a5fa 0%, transparent 55%)"></div>
        <div class="relative flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">{{ __('parent.guardian_portal') }}</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-1">{{ __('parent.welcome') }}, {{ auth()->user()->name }}</h1>
                <p class="text-blue-100 text-sm">{{ $children->count() === 1 ? __('parent.stay_updated') : __('parent.stay_updated_plural') }}</p>
            </div>
            @if(($unreadMessages ?? 0) > 0)
            <a href="{{ route('parent.messages') }}" class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-xl text-sm font-semibold">
                {{ __('parent.unread_messages', ['count' => $unreadMessages]) }}
            </a>
            @endif
        </div>
    </div>

    {{-- Quick message teachers & counsellors --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <a href="{{ route('parent.messages') }}?compose=teacher" class="flex items-center gap-4 p-5 rounded-2xl bg-white border border-blue-100 hover:border-blue-400 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">👨‍🏫</div>
            <div>
                <p class="font-bold text-black/85">{{ __('parent.message_teacher') }}</p>
                <p class="text-xs text-black/45">{{ __('parent.communicate_desc') }}</p>
            </div>
        </a>
        <a href="{{ route('parent.messages') }}?compose=counsellor" class="flex items-center gap-4 p-5 rounded-2xl bg-white border border-blue-100 hover:border-blue-400 hover:shadow-md transition-all">
            <div class="w-12 h-12 rounded-xl bg-blue-100 flex items-center justify-center text-xl">🎯</div>
            <div>
                <p class="font-bold text-black/85">{{ __('parent.message_counsellor') }}</p>
                <p class="text-xs text-black/45">{{ __('parent.communicate_desc') }}</p>
            </div>
        </a>
    </div>

    {{-- Communication CTA --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <a href="{{ route('parent.messages') }}" class="group p-5 rounded-2xl bg-white border border-black/10 hover:border-blue-300 hover:shadow-md transition-all">
            <p class="font-bold text-black/85 group-hover:text-blue-700">{{ __('parent.open_messages') }}</p>
            <p class="text-xs text-black/45 mt-1">{{ __('parent.communicate_desc') }}</p>
        </a>
        <a href="{{ route('parent.progress') }}" class="group p-5 rounded-2xl bg-white border border-black/10 hover:border-blue-300 hover:shadow-md transition-all">
            <p class="font-bold text-black/85">{{ __('parent.progress') }}</p>
            <p class="text-xs text-black/45 mt-1">{{ __('parent.academic_progress') }}</p>
        </a>
        <a href="{{ route('parent.reports') }}" class="group p-5 rounded-2xl bg-white border border-black/10 hover:border-blue-300 hover:shadow-md transition-all">
            <p class="font-bold text-black/85">{{ __('parent.reports') }}</p>
            <p class="text-xs text-black/45 mt-1">{{ __('parent.download_report') }}</p>
        </a>
    </div>

    @if($children->isEmpty())
    <div class="bg-white rounded-2xl p-12 shadow-sm border border-black/10 text-center">
        <h3 class="font-bold text-black/60 mb-1">{{ __('parent.no_children') }}</h3>
        <p class="text-sm text-black/40">{{ __('parent.contact_school') }}</p>
    </div>
    @else

    @php $allAlerts = $children->flatMap(fn($c) => $c->risk_alerts ?? [])->unique(); @endphp
    @if($allAlerts->isNotEmpty())
    <div class="bg-rose-50 border border-rose-200 rounded-2xl p-5">
        <h2 class="text-sm font-bold text-rose-800 uppercase tracking-wider mb-3">{{ __('parent.risk_alerts') }}</h2>
        <ul class="space-y-2">
            @foreach($allAlerts as $alert)
            <li class="text-sm text-rose-900 flex items-start gap-2">
                <span class="text-rose-500 mt-0.5">•</span>{{ $alert }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        @foreach($children as $child)
        @php
            $score = $child->best_match_score ? round($child->best_match_score) : null;
            $avg = $child->avg_score ? round($child->avg_score) : null;
        @endphp
        <div class="bg-white rounded-2xl border border-black/10 overflow-hidden shadow-sm hover:shadow-lg transition-shadow">
            <div class="bg-gradient-to-r from-blue-700 to-blue-700 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-white/20 flex items-center justify-center text-white font-bold text-lg">{{ strtoupper(substr($child->name, 0, 2)) }}</div>
                        <div>
                            <h2 class="font-bold text-white">{{ $child->name }}</h2>
                            <p class="text-xs text-blue-200">{{ $child->studentProfile->form_level ?? '' }}</p>
                        </div>
                    </div>
                    @if($score)
                    <span class="px-3 py-1 bg-white/20 rounded-full text-xs font-semibold text-white">{{ $score }}% {{ __('parent.match') }}</span>
                    @else
                    <span class="px-3 py-1 bg-white/10 rounded-full text-xs text-blue-200">{{ __('parent.no_assessment') }}</span>
                    @endif
                </div>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <p class="text-xs text-black/40 uppercase font-semibold mb-1">{{ __('parent.recommended_career') }}</p>
                    <p class="font-semibold text-black/80">{{ $child->top_career?->recommended?->title ?? __('parent.assessment_pending') }}</p>
                </div>
                @if($child->top_subject_combo?->recommended)
                <div>
                    <p class="text-xs text-black/40 uppercase font-semibold mb-1">{{ __('parent.subject_recommendation') }}</p>
                    <p class="font-semibold text-black/80">{{ $child->top_subject_combo->recommended->name }}</p>
                    <p class="text-xs text-black/45 mt-1">{{ __('parent.combo_explanation') }}</p>
                </div>
                @endif
                @if(!empty($child->strengths))
                <div>
                    <p class="text-xs text-black/40 uppercase font-semibold mb-1">{{ __('parent.strengths') }}</p>
                    <p class="text-sm text-blue-700">{{ implode(', ', array_slice($child->strengths, 0, 3)) }}</p>
                </div>
                @endif
                @if(!empty($child->weaknesses))
                <div>
                    <p class="text-xs text-black/40 uppercase font-semibold mb-1">{{ __('parent.weaknesses') }}</p>
                    <p class="text-sm text-amber-700">{{ implode(', ', array_slice($child->weaknesses, 0, 3)) }}</p>
                </div>
                @endif
                @if($avg !== null)
                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="text-black/50">{{ __('parent.academic_progress') }}</span>
                        <span class="font-semibold text-blue-600">{{ $avg }}%</span>
                    </div>
                    <div class="h-2 bg-black/[0.04] rounded-full overflow-hidden">
                        <div class="h-full bg-blue-500 rounded-full" style="width: {{ min(100, $avg) }}%"></div>
                    </div>
                </div>
                @else
                <p class="text-xs text-black/40">{{ __('parent.no_grades') }}</p>
                @endif
                <div class="flex flex-wrap gap-2 pt-2">
                    <a href="{{ route('parent.child.detail', $child) }}" class="flex-1 text-center px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700">{{ __('parent.view_details') }}</a>
                    <a href="{{ route('parent.messages', ['with' => $child->id]) }}" class="px-4 py-2.5 border border-blue-200 text-blue-700 rounded-xl text-sm font-semibold hover:bg-blue-50">{{ __('parent.message_child') }}</a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-black/10">
        <h2 class="text-lg font-bold text-black/80 mb-4">{{ __('parent.recent_activity') }}</h2>
        @if($recentActivity->isEmpty())
        <p class="text-sm text-black/40 text-center py-6">{{ __('parent.no_activity') }}</p>
        @else
        <div class="space-y-3">
            @foreach($recentActivity as $activity)
            <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-black/[0.02]">
                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-medium text-black/70">{{ $activity->student->name ?? '' }} {{ __('parent.assessment_completed') }}</p>
                    <p class="text-xs text-black/40">{{ $activity->completed_at?->diffForHumans() ?? $activity->updated_at->diffForHumans() }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
