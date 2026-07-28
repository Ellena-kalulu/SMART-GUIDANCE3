@extends('layouts.dashboard')

@section('title', __('parent.progress_reports'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">{{ __('parent.guardian') }}</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">{{ __('parent.progress_reports') }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden shadow-lg">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #60a5fa 0%, transparent 60%)"></div>
        <div class="relative">
            <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">{{ __('parent.guardian_portal') }}</p>
            <h1 class="text-2xl md:text-3xl font-bold mb-1">{{ __('parent.progress_reports') }}</h1>
            <p class="text-white/70 text-sm max-w-2xl">{{ __('parent.progress_page_desc') }}</p>
        </div>
    </div>

    {{-- Child Selector (if multiple children) --}}
    @if(isset($children) && $children->count() > 1)
    <div class="flex gap-2 flex-wrap">
        <button onclick="showChild('all')" id="btn-all"
                class="px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 text-white transition-colors">
            {{ __('parent.all_children') }}
        </button>
        @foreach($children as $child)
        <button onclick="showChild({{ $child->id }})" id="btn-{{ $child->id }}"
                class="px-4 py-2 rounded-xl text-sm font-semibold bg-white border border-blue-200 text-blue-600
                       hover:border-blue-400 hover:bg-blue-50 transition-colors">
            {{ $child->name }}
        </button>
        @endforeach
    </div>
    @endif

    {{-- Per-Child Progress Sections --}}
    @forelse($children ?? [] as $child)
    @php
        $topCareer  = $child->recommendations->where('type','career')->sortByDesc('confidence_score')->first();
        $topSubject = $child->recommendations->where('type','subject_combination')->sortByDesc('confidence_score')->first();
        $allCareers = $child->recommendations->where('type','career')->sortByDesc('confidence_score')->take(3);
        $uniRecs    = $child->recommendations->where('type','university_program')->sortByDesc('confidence_score')->take(3);
        $avgScore   = $child->academicResults->avg('score');
        $uniElig    = $child->recommendations->where('type','university_program')->where('confidence_score','>=',80)->count();
        $assessed   = $child->assessmentAttempts->where('status','completed')->isNotEmpty();
        $resultsByTerm = $child->academicResults->sortBy('term')->groupBy('term');

        // Risk detection: subjects below pass mark
        $weakSubjects = $child->academicResults
            ->sortBy('score')
            ->filter(fn($r) => $r->score < 55)
            ->groupBy(fn($r) => $r->subject?->name)
            ->map(fn($g) => round($g->avg('score'), 1))
            ->take(3);

        // Top performing subjects
        $strongSubjects = $child->academicResults
            ->sortByDesc('score')
            ->filter(fn($r) => $r->score >= 70)
            ->groupBy(fn($r) => $r->subject?->name)
            ->map(fn($g) => round($g->avg('score'), 1))
            ->take(3);

        // Subject combination subjects list
        $comboSubjects = $topSubject?->recommended?->subjects ?? collect();
    @endphp

    <div class="child-section space-y-5" data-child="{{ $child->id }}">

        {{-- Child Header --}}
        <div class="flex items-center gap-3 py-2 border-b border-blue-100">
            <div class="w-10 h-10 bg-blue-600 rounded-xl flex items-center justify-center text-white font-bold text-sm shadow-md shrink-0">
                {{ strtoupper(substr($child->name, 0, 2)) }}
            </div>
            <div>
                <h2 class="font-bold text-blue-900">{{ $child->name }}</h2>
                <p class="text-xs text-blue-500 font-medium">{{ $child->studentProfile->form_level ?? '' }}</p>
            </div>
        </div>

        {{-- Summary Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100 text-center">
                <p class="font-black text-lg {{ $assessed ? 'text-emerald-600' : 'text-black/40' }}">
                    {{ $assessed ? __('parent.done') : __('parent.pending') }}
                </p>
                <p class="text-xs text-black/50 mt-0.5">{{ __('parent.assessment') }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100 text-center">
                <p class="font-black text-lg {{ $avgScore >= 65 ? 'text-emerald-600' : ($avgScore >= 50 ? 'text-amber-600' : 'text-rose-500') }}">
                    {{ $avgScore ? round($avgScore).'%' : '—' }}
                </p>
                <p class="text-xs text-black/50 mt-0.5">{{ __('parent.avg_score') }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100 text-center">
                <p class="font-bold text-sm text-blue-700 leading-tight truncate">
                    {{ $topCareer?->recommended?->title ?? '—' }}
                </p>
                <p class="text-xs text-black/50 mt-0.5">{{ __('parent.top_career') }}</p>
            </div>
            <div class="bg-white rounded-xl p-4 shadow-sm border border-blue-100 text-center">
                <p class="font-black text-lg text-blue-600">{{ $uniElig }}</p>
                <p class="text-xs text-black/50 mt-0.5">{{ __('parent.uni_eligible') }}</p>
            </div>
        </div>

        {{-- Career Recommendations & Subject Guidance --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            {{-- Career Recommendations --}}
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-blue-50/60 border-b border-blue-100 flex items-center gap-2">
                    <div class="w-6 h-6 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-blue-900 text-sm">{{ __('parent.career_recommendations') }}</h3>
                </div>
                @if($allCareers->isNotEmpty())
                <div class="divide-y divide-blue-50">
                    @foreach($allCareers as $i => $rec)
                    <div class="px-5 py-3 flex items-center gap-3">
                        <span class="w-5 h-5 rounded-full text-[10px] font-black flex items-center justify-center shrink-0
                            {{ $i === 0 ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-600' }}">{{ $i + 1 }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-semibold text-black/80 text-sm truncate">{{ $rec->recommended?->title ?? '—' }}</p>
                            <p class="text-xs text-black/45">{{ $rec->recommended?->category ?? '' }}</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0
                            {{ $rec->confidence_score >= 75 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200' }}">
                            {{ round($rec->confidence_score) }}%
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-6 text-center text-sm text-black/40">
                    {{ $assessed ? 'No career recommendations yet.' : __('parent.assessment_pending') }}
                </div>
                @endif
            </div>

            {{-- Subject Combination Guidance --}}
            <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
                <div class="px-5 py-3.5 bg-blue-50/60 border-b border-blue-100 flex items-center gap-2">
                    <div class="w-6 h-6 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="font-bold text-blue-900 text-sm">{{ __('parent.subject_recommendation') }}</h3>
                </div>
                @if($topSubject)
                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-blue-700 text-sm">{{ $topSubject->recommended?->name ?? '—' }}</p>
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-bold border border-blue-200">
                            {{ round($topSubject->confidence_score) }}%
                        </span>
                    </div>
                    @if($comboSubjects->isNotEmpty())
                    <div>
                        <p class="text-[10px] font-bold text-black/40 uppercase mb-1.5">Subjects in this combination</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($comboSubjects as $subj)
                            <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-lg border border-blue-100 font-medium">
                                {{ $subj->name }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                    @if($topSubject->reason)
                    <p class="text-xs text-black/50 leading-relaxed border-t border-blue-50 pt-3">{{ $topSubject->reason }}</p>
                    @endif
                    <p class="text-[10px] text-blue-500 font-medium">{{ __('parent.combo_explanation') }}</p>
                </div>
                @else
                <div class="px-5 py-6 text-center text-sm text-black/40">
                    {{ $assessed ? 'No subject combination recommended yet.' : __('parent.assessment_pending') }}
                </div>
                @endif
            </div>

        </div>

        {{-- Strengths & Risk Alerts --}}
        @if($strongSubjects->isNotEmpty() || $weakSubjects->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            @if($strongSubjects->isNotEmpty())
            <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <h4 class="font-bold text-emerald-800 text-sm">{{ __('parent.strengths') }}</h4>
                </div>
                <div class="space-y-2">
                    @foreach($strongSubjects as $name => $score)
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-emerald-800 font-medium truncate">{{ $name }}</span>
                        <span class="font-bold text-emerald-700 text-sm shrink-0">{{ $score }}%</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($weakSubjects->isNotEmpty())
            <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <h4 class="font-bold text-amber-800 text-sm">{{ __('parent.weaknesses') }}</h4>
                </div>
                <div class="space-y-2">
                    @foreach($weakSubjects as $name => $score)
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-sm text-amber-800 font-medium truncate">{{ $name }}</span>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="font-bold text-amber-700 text-sm">{{ $score }}%</span>
                            <span class="text-[10px] text-amber-600">needs +{{ max(0, 55 - $score) }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-amber-700 mt-3 pt-2 border-t border-amber-200">
                    Consider extra tutoring or practice in these subjects to strengthen the recommendation.
                </p>
            </div>
            @endif
        </div>
        @endif

        {{-- Academic Results by Term --}}
        @foreach($resultsByTerm as $term => $results)
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-blue-100 bg-blue-50/40 flex items-center justify-between">
                <h3 class="font-semibold text-blue-900 text-sm">{{ __('parent.term') }} {{ $term }}</h3>
                @php $termAvg = round($results->avg('score'), 1); @endphp
                <span class="text-xs font-bold px-3 py-1 rounded-full border
                    {{ $termAvg >= 65 ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($termAvg >= 50 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-rose-50 text-rose-700 border-rose-200') }}">
                    {{ __('parent.term_avg', ['avg' => $termAvg]) }}
                </span>
            </div>
            <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @foreach($results as $result)
                @php
                    $s = $result->score;
                    $barColor = $s >= 70 ? 'bg-emerald-500' : ($s >= 55 ? 'bg-blue-500' : ($s >= 40 ? 'bg-amber-500' : 'bg-rose-400'));
                    $textColor = $s >= 70 ? 'text-emerald-700' : ($s >= 55 ? 'text-blue-600' : ($s >= 40 ? 'text-amber-600' : 'text-rose-500'));
                @endphp
                <div class="bg-blue-50/40 rounded-xl p-3 border border-blue-100 hover:border-blue-200 transition-colors">
                    <p class="text-xs text-black/50 truncate mb-2">{{ $result->subject->name ?? __('parent.subject') }}</p>
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex-1 h-1.5 bg-blue-100 rounded-full overflow-hidden">
                            <div class="{{ $barColor }} h-full rounded-full" style="width:{{ min($s,100) }}%"></div>
                        </div>
                        <span class="text-sm font-black {{ $textColor }} shrink-0">{{ $s }}%</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach

        @if($resultsByTerm->isEmpty())
        <div class="bg-white rounded-2xl p-8 shadow-sm border border-blue-100 text-center text-sm text-black/40">
            {{ __('parent.no_results_child', ['name' => $child->name]) }}
        </div>
        @endif

        {{-- University Programmes --}}
        @if($uniRecs->isNotEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-blue-100 overflow-hidden">
            <div class="px-5 py-3.5 border-b border-blue-100 bg-blue-50/40">
                <h3 class="font-semibold text-blue-900 text-sm">{{ __('parent.university_programs') }}</h3>
                <p class="text-xs text-black/40 mt-0.5">University programmes {{ $child->name }} may be eligible for</p>
            </div>
            <div class="divide-y divide-blue-50">
                @foreach($uniRecs as $rec)
                <div class="px-5 py-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="font-semibold text-black/80 text-sm truncate">{{ $rec->recommended->name ?? 'Programme' }}</p>
                        <p class="text-xs text-black/50">{{ $rec->recommended->university ?? '' }}</p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-bold shrink-0
                        {{ $rec->confidence_score >= 80 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                        {{ __('parent.match_percent', ['score' => round($rec->confidence_score)]) }}
                    </span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Parent Guidance Box --}}
        <div class="bg-blue-50 rounded-2xl border border-blue-200 p-5">
            <h4 class="font-bold text-blue-900 text-sm mb-3">How You Can Help {{ $child->name }}</h4>
            <div class="space-y-2.5 text-sm text-blue-800">
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Discuss {{ $topCareer?->recommended?->title ? "the career recommendation (" . $topCareer->recommended->title . ")" : "career options" }} with {{ $child->name }} to see if it aligns with their own goals.</span>
                </div>
                @if($topSubject)
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                    <span>The recommended subject combination is <strong>{{ $topSubject->recommended?->name }}</strong>. Speak with the school to confirm this during subject selection.</span>
                </div>
                @endif
                @if($weakSubjects->isNotEmpty())
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-amber-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $child->name }} needs support in <strong>{{ $weakSubjects->keys()->implode(', ') }}</strong>. Consider extra tuition or home practice in these areas.</span>
                </div>
                @endif
                <div class="flex items-start gap-2.5">
                    <svg class="w-4 h-4 text-blue-500 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book a counselling session at school to have a career guide review and approve {{ $child->name }}'s subject combination.</span>
                </div>
            </div>
        </div>

    </div>{{-- end child-section --}}
    @empty
    <div class="bg-white rounded-2xl p-12 shadow-sm border border-blue-100 text-center">
        <p class="text-black/40 text-sm">{{ __('parent.contact_school') }}</p>
    </div>
    @endforelse

</div>

@push('scripts')
<script>
function showChild(id) {
    document.querySelectorAll('.child-section').forEach(s => {
        s.style.display = (id === 'all' || s.dataset.child == id) ? '' : 'none';
    });
    document.querySelectorAll('[id^="btn-"]').forEach(b => {
        b.classList.remove('bg-blue-600','text-white');
        b.classList.add('bg-white','border','border-blue-200','text-blue-600');
    });
    const btn = document.getElementById('btn-' + id);
    if (btn) {
        btn.classList.remove('bg-white','border','border-blue-200','text-blue-600');
        btn.classList.add('bg-blue-600','text-white');
    }
}
</script>
@endpush
@endsection
