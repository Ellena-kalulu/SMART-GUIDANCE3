@extends('layouts.dashboard')

@section('title', 'My Recommendations')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">My Recommendations</span>
</nav>
@endsection

@php
    $sortedCareers = $careers->sortByDesc('confidence_score')->values();
@endphp

@section('content')
<div class="space-y-8" x-data="careerRecommendations()">

    <div class="rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 md:p-8 text-white shadow-lg">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-2">Personalised For You</p>
                <h1 class="text-2xl md:text-3xl font-bold">Career Recommendations</h1>
                <p class="text-white/80 text-sm mt-2 max-w-2xl">
                    Matched using your <strong>self assessment</strong> and <strong>teacher-uploaded grades</strong>.
                    Tap <strong>Universities</strong>, <strong>Subjects</strong>, or <strong>Details</strong> at the bottom of each card.
                </p>
            </div>
        </div>
    </div>

    @if($sortedCareers->isEmpty())
    <div class="bg-white rounded-2xl border border-dashed border-blue-200 p-12 text-center">
        <h3 class="font-bold text-black/80 text-lg mb-2">No recommendations yet</h3>
        <p class="text-sm text-black/50 mb-6">Complete the self assessment survey first.</p>
        <a href="{{ route('student.assessment') }}" class="inline-flex px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold text-sm hover:bg-blue-700">Take Self Assessment</a>
    </div>
    @else

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
        @foreach($sortedCareers as $rec)
        @php
            $career = $rec->recommended;
            if (!$career) continue;
            $career->loadMissing(['universityPrograms', 'requiredSubjects']);
            // Match subject combo to the career's category (Science vs Humanities)
            $scienceCats = ['science', 'technology', 'engineering', 'mathematics', 'health', 'medical', 'stem', 'agriculture', 'nursing'];
            $careerCatLower = strtolower($career->category ?? '');
            $isScience = collect($scienceCats)->contains(fn($c) => str_contains($careerCatLower, $c));
            $matchingCombo = $subjectCombos->first(fn($c) => str_contains(strtolower($c->recommended?->name ?? ''), $isScience ? 'science' : 'humanities'))
                ?? $subjectCombos->first(fn($c) => str_contains(strtolower($c->recommended?->name ?? ''), $isScience ? 'humanities' : 'science'))
                ?? $subjectCombos->first();
            $combo = $matchingCombo?->recommended;
        @endphp
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm hover:shadow-lg hover:border-blue-300 transition-all flex flex-col overflow-hidden">
            <div class="p-6 flex flex-col flex-1">
                <div class="flex items-center justify-between gap-2 mb-2">
                    <span class="inline-block px-2.5 py-1 bg-blue-100 text-blue-800 text-[10px] font-bold uppercase tracking-wider rounded-md">{{ $career->category }}</span>
                </div>
                <h3 class="font-bold text-black/90 text-lg leading-snug mb-1">{{ $career->title }}</h3>
                <p class="text-sm text-black/55 leading-relaxed mb-4 flex-1 line-clamp-3">{{ Str::limit($career->description, 120) }}</p>

                @if($career->required_skills)
                <div class="flex flex-wrap gap-1.5 mb-4">
                    @foreach(array_slice(explode(',', $career->required_skills), 0, 3) as $skill)
                    <span class="px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded-lg border border-blue-100">{{ trim($skill) }}</span>
                    @endforeach
                </div>
                @endif

                {{-- Buttons at bottom of card content --}}
                <div class="grid grid-cols-3 gap-2 mb-4 mt-auto">
                    <button type="button" @click="openUniversities({{ json_encode([
                        'title' => $career->title,
                        'programs' => $career->universityPrograms->map(fn($p) => [
                            'name' => $p->name,
                            'university' => $p->university,
                            'faculty' => $p->faculty,
                            'entry' => $p->entry_requirements,
                            'subjects' => $p->requiredSubjects->map(fn($s) => [
                                'name' => $s->name,
                                'min' => $s->pivot->minimum_grade ?? 'C',
                            ])->values(),
                        ])->values(),
                    ]) }})"
                            class="py-2.5 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-sm hover:shadow-md transition-all">
                        Universities
                    </button>
                    <button type="button" @click="openSubjects({{ json_encode([
                        'title' => $career->title,
                        'required' => $career->requiredSubjects->pluck('name'),
                        'combo' => $combo ? [
                            'name' => $combo->name,
                            'score' => round($matchingCombo->confidence_score),
                            'description' => $combo->description,
                            'subjects' => $combo->subjects->pluck('name'),
                        ] : null,
                    ]) }})"
                            class="py-2.5 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-sm hover:shadow-md transition-all">
                        Subjects
                    </button>
                    <button type="button" @click="openDetails({{ json_encode([
                        'title' => $career->title,
                        'category' => $career->category,
                        'description' => $career->description,
                        'reason' => $rec->reason,
                        'skills' => $career->required_skills,
                    ]) }})"
                            class="py-2.5 text-xs font-bold rounded-xl bg-blue-600 text-white hover:bg-blue-700 shadow-sm hover:shadow-md transition-all">
                        Details
                    </button>
                </div>

                <div class="flex gap-2 pt-3 border-t border-blue-100">
                    @if(in_array($rec->status, ['pending', 'viewed']))
                    <form action="{{ route('student.recommendations.save', $rec) }}" method="POST" class="flex-1">@csrf
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold">Save</button>
                    </form>
                    <form action="{{ route('student.recommendations.dismiss', $rec) }}" method="POST">@csrf
                        <button type="submit" class="px-3 py-2.5 border border-blue-200 text-blue-400 rounded-xl text-xs hover:bg-blue-50" title="Dismiss">✕</button>
                    </form>
                    @elseif($rec->status === 'accepted')
                    <span class="flex-1 py-2.5 bg-blue-100 text-blue-800 rounded-xl text-xs font-bold text-center">✓ Saved</span>
                    @else
                    <span class="flex-1 py-2.5 bg-blue-50 text-blue-400 rounded-xl text-xs text-center">Dismissed</span>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="rounded-2xl bg-gradient-to-r from-blue-700 to-blue-900 p-6 md:p-8 text-white shadow-lg">
        <h3 class="font-bold text-lg">Need more help?</h3>
        <p class="text-sm text-white/80 mt-2">Book a counselling session to discuss your results.</p>
        <div class="mt-4 flex flex-wrap gap-3">
            <a href="{{ route('student.appointments') }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 bg-white text-blue-800 rounded-xl font-bold text-sm hover:bg-blue-50 shadow-sm transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Book a Counselling Session
            </a>
            <a href="{{ route('student.subject.combinations') }}" class="px-6 py-3 bg-white/10 text-white border border-white/20 rounded-xl font-bold text-sm hover:bg-white/20">Subject Combinations</a>
        </div>
    </div>
    @endif

    {{-- Universities Modal --}}
    <div x-show="uniModal.open" x-cloak @click.self="closeModals()"
         class="fixed inset-0 bg-black/50 z-[9998] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="sticky top-0 bg-white border-b border-blue-100 px-6 py-4 flex justify-between items-start">
                <div>
                    <p class="text-xs font-bold text-blue-600 uppercase">Universities for</p>
                    <h2 class="text-lg font-bold text-black/85" x-text="uniModal.title"></h2>
                </div>
                <button type="button" @click="closeModals()" class="text-black/40 hover:text-black/70 text-xl leading-none">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <template x-for="(p, i) in uniModal.programs" :key="i">
                    <div class="rounded-xl border border-blue-100 bg-blue-50/30 p-4">
                        <h3 class="font-bold text-black/85 text-sm" x-text="p.name"></h3>
                        <p class="text-xs text-black/50 mt-1"><span x-text="p.university"></span><span x-show="p.faculty"> &bull; <span x-text="p.faculty"></span></span></p>
                        <p class="text-xs text-black/45 mt-2 italic" x-show="p.entry" x-text="p.entry"></p>
                        <template x-if="p.subjects && p.subjects.length">
                            <div class="mt-3">
                                <p class="text-[10px] font-bold text-blue-600 uppercase mb-2">Required Subjects</p>
                                <div class="flex flex-wrap gap-2">
                                    <template x-for="s in p.subjects" :key="s.name">
                                        <span class="px-2 py-1 bg-white text-blue-700 text-xs rounded-lg border border-blue-200 font-medium" x-text="s.name + ' — Min. ' + s.min"></span>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
                <p x-show="!uniModal.programs || uniModal.programs.length === 0" class="text-sm text-black/50 text-center py-4">No university programmes listed for this career yet.</p>
            </div>
        </div>
    </div>

    {{-- Subjects Modal --}}
    <div x-show="subModal.open" x-cloak @click.self="closeModals()"
         class="fixed inset-0 bg-black/50 z-[9998] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto" @click.stop>
            <div class="px-6 py-4 border-b border-blue-100 flex justify-between items-center">
                <h2 class="font-bold text-black/85">Subjects for <span x-text="subModal.title" class="text-blue-600"></span></h2>
                <button type="button" @click="closeModals()" class="text-black/40 hover:text-black/70 text-xl">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <template x-if="subModal.combo">
                    <div class="rounded-xl border border-blue-200 bg-blue-50/50 p-4">
                        <div class="flex justify-between items-start mb-2">
                            <h3 class="font-bold text-black/85" x-text="subModal.combo.name"></h3>
                            <span class="text-lg font-black text-blue-600" x-text="subModal.combo.score + '%'"></span>
                        </div>
                        <p class="text-xs text-black/50 mb-3" x-text="subModal.combo.description"></p>
                        <div class="flex flex-wrap gap-1.5">
                            <template x-for="s in subModal.combo.subjects" :key="s">
                                <span class="px-2.5 py-1 bg-white text-blue-700 text-xs rounded-lg border border-blue-200 font-medium" x-text="s"></span>
                            </template>
                        </div>
                    </div>
                </template>
                <div x-show="subModal.required && subModal.required.length">
                    <p class="text-xs font-bold text-blue-600 uppercase mb-2">Required for this career</p>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="s in subModal.required" :key="s">
                            <span class="px-2.5 py-1 bg-blue-50 text-blue-800 text-xs rounded-lg border border-blue-100" x-text="s"></span>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Details Modal --}}
    <div x-show="detailModal.open" x-cloak @click.self="closeModals()"
         class="fixed inset-0 bg-black/50 z-[9998] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6" @click.stop>
            <button type="button" @click="closeModals()" class="float-right text-black/40 hover:text-black/70 text-xl">&times;</button>
            <h2 class="font-bold text-xl text-black/85 pr-8" x-text="detailModal.title"></h2>
            <p class="text-xs text-blue-600 font-bold uppercase mt-1" x-text="detailModal.category"></p>
            <p class="text-sm text-black/65 mt-4 leading-relaxed" x-text="detailModal.description"></p>
            <p class="text-sm text-black/50 italic mt-3 p-3 bg-blue-50 rounded-xl border border-blue-100" x-show="detailModal.reason" x-text="detailModal.reason"></p>
            <button type="button" @click="closeModals()" class="mt-6 w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold">Close</button>
        </div>
    </div>

</div>

@push('scripts')
<script>
function careerRecommendations() {
    return {
        uniModal: { open: false, title: '', programs: [] },
        subModal: { open: false, title: '', required: [], combo: null },
        detailModal: { open: false, title: '', category: '', description: '', reason: '' },
        openUniversities(data) {
            this.closeModals();
            this.uniModal = { open: true, ...data };
            document.body.style.overflow = 'hidden';
        },
        openSubjects(data) {
            this.closeModals();
            this.subModal = { open: true, ...data };
            document.body.style.overflow = 'hidden';
        },
        openDetails(data) {
            this.closeModals();
            this.detailModal = { open: true, ...data };
            document.body.style.overflow = 'hidden';
        },
        closeModals() {
            this.uniModal.open = false;
            this.subModal.open = false;
            this.detailModal.open = false;
            document.body.style.overflow = '';
        },
    };
}
</script>
@endpush
@endsection

