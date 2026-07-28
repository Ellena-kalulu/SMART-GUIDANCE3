@extends('layouts.dashboard')

@section('title', 'University Programs Library')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">University Programs</span>
</nav>
@endsection

@php
function uniGradeLabel(int $g): array {
    if ($g <= 2) return ['Distinction', 'bg-purple-100 text-purple-700 border-purple-200'];
    if ($g <= 4) return ['Strong Credit', 'bg-blue-100 text-blue-700 border-blue-200'];
    if ($g <= 6) return ['Credit', 'bg-green-100 text-green-700 border-green-200'];
    return ['Pass', 'bg-gray-100 text-gray-600 border-gray-200'];
}
@endphp

@section('content')
<div x-data="{
        search: '',
        faculty: '',
        expanded: {},
        programs: {{ \Illuminate\Support\Js::from($programs->map(fn($p) => ['n' => $p->name, 'u' => $p->university, 'f' => $p->faculty])->values()) }},
        matches(n, u, f) {
            const q = this.search.toLowerCase();
            return (q === '' || n.toLowerCase().includes(q) || u.toLowerCase().includes(q) || f.toLowerCase().includes(q))
                && (this.faculty === '' || f === this.faculty);
        },
        get anyMatch() {
            return this.programs.some(p => this.matches(p.n, p.u, p.f));
        },
        toggle(k) { this.expanded[k] = !this.expanded[k]; }
    }" class="space-y-6">

    {{-- Page header --}}
    <div class="flex items-start justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-black text-black">University Programs Library</h1>
            <p class="text-black/50 text-sm mt-1">
                Browse all {{ $totalPrograms }} programs across {{ $universities->count() }} universities and check your eligibility.
            </p>
        </div>
        <a href="{{ route('student.recommendations', ['type' => 'university_program']) }}"
           class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            My Recommendations
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl border border-black/10 p-5">
            <p class="text-2xl font-black text-blue-700">{{ $totalPrograms }}</p>
            <p class="text-sm text-black/50 mt-0.5">Total Programs</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5">
            <p class="text-2xl font-black text-blue-700">{{ $universities->count() }}</p>
            <p class="text-sm text-black/50 mt-0.5">Universities</p>
        </div>
        <div class="bg-white rounded-2xl border border-black/10 p-5">
            <p class="text-2xl font-black text-blue-700">{{ count($faculties) }}</p>
            <p class="text-sm text-black/50 mt-0.5">Faculties</p>
        </div>
    </div>

    {{-- No academic data notice --}}
    @if(!$hasAcademicData)
    <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-yellow-800">
            <strong>No academic results on record.</strong>
            Eligibility is based on the subjects you study. Ask your teacher to add your academic results so you can see which programs you qualify for.
        </p>
    </div>
    @endif

    {{-- Search & filter --}}
    <div class="bg-white rounded-2xl border border-black/10 p-4 flex flex-wrap gap-3">
        <div class="flex-1 min-w-48 relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-black/30 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" x-model="search"
                   placeholder="Search programs, universities, faculties..."
                   class="w-full pl-9 pr-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-300">
        </div>
        <select x-model="faculty"
                class="border border-black/15 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
            <option value="">All Faculties</option>
            @foreach($faculties as $f)
            <option value="{{ $f }}">{{ $f }}</option>
            @endforeach
        </select>
        <button x-show="search !== '' || faculty !== ''" x-cloak
                @click="search = ''; faculty = ''"
                class="px-4 py-2.5 text-sm text-black/50 border border-black/15 rounded-xl hover:bg-black/[0.03] transition-colors">
            Clear
        </button>
    </div>

    {{-- University sections --}}
    @foreach($universities as $uniName => $uniPrograms)
    @php $slug = \Illuminate\Support\Str::slug($uniName); @endphp
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden"
         x-show="programs.filter(p => p.u === {{ \Illuminate\Support\Js::from($uniName) }}).some(p => matches(p.n, p.u, p.f))">

        {{-- University header --}}
        <div class="px-6 py-4 bg-gradient-to-r from-blue-700 to-blue-600">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base">{{ $uniName }}</h2>
                    <p class="text-blue-200 text-xs">
                        {{ $uniPrograms->count() }} {{ \Illuminate\Support\Str::plural('program', $uniPrograms->count()) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Program rows --}}
        <div class="divide-y divide-black/5">
            @foreach($uniPrograms as $program)
            @php
                $eligible = $program->is_eligible;
                $reqSubs  = $program->requiredSubjects;
                $key      = $slug . '-' . $program->id;
                $matchKey = $key . '-match';
            @endphp
            <div x-show="matches({{ \Illuminate\Support\Js::from($program->name) }}, {{ \Illuminate\Support\Js::from($program->university) }}, {{ \Illuminate\Support\Js::from($program->faculty) }})"
                 class="p-5">

                {{-- Program title row --}}
                <div class="flex items-start justify-between gap-4 flex-wrap">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap mb-1">
                            <h3 class="font-bold text-black/80 text-sm">{{ $program->name }}</h3>
                        </div>
                        <p class="text-xs text-black/40">
                            {{ $program->faculty }}
                            @if($program->minimum_points)
                            &bull; Min. {{ $program->minimum_points }} points
                            @endif
                        </p>
                    </div>

                    <button @click="toggle({{ \Illuminate\Support\Js::from($key) }})"
                            class="shrink-0 flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-blue-600 border border-blue-200 rounded-lg hover:bg-blue-50 transition-colors">
                        <span x-text="expanded[{{ \Illuminate\Support\Js::from($key) }}] ? 'Hide' : 'View Requirements'"></span>
                        <svg class="w-3.5 h-3.5 transition-transform duration-150"
                             :class="expanded[{{ \Illuminate\Support\Js::from($key) }}] ? 'rotate-180' : ''"
                             fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                {{-- Expandable details --}}
                <div x-show="expanded[{{ \Illuminate\Support\Js::from($key) }}]"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 -translate-y-1"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="mt-4 pt-4 border-t border-black/5 space-y-4">

                    {{-- Required subjects with minimum MSCE grade --}}
                    <div>
                        <p class="text-xs font-semibold text-black/40 uppercase tracking-wide mb-2">Required Subjects &amp; Minimum MSCE Grade</p>
                        @if($reqSubs->isNotEmpty())
                        <div class="flex flex-wrap gap-2">
                            @foreach($reqSubs as $sub)
                            @php [$grLabel, $grColours] = uniGradeLabel((int) $sub->pivot->minimum_grade); @endphp
                            <div class="inline-flex items-center rounded-lg border overflow-hidden {{ $grColours }}">
                                <span class="px-3 py-1.5 text-xs font-semibold bg-white/60 border-r {{ str_replace('bg-', 'border-', explode(' ', $grColours)[0]) }}">
                                    {{ $sub->name }}
                                </span>
                                <span class="px-2.5 py-1.5 text-xs font-bold">
                                    Min. {{ $sub->pivot->minimum_grade }} — {{ $grLabel }}
                                </span>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-black/40 italic">No specific subject requirements listed.</p>
                        @endif
                    </div>

                    {{-- Entry requirements --}}
                    @if($program->entry_requirements)
                    <div>
                        <p class="text-xs font-semibold text-black/40 uppercase tracking-wide mb-1.5">Entry Requirements</p>
                        <p class="text-sm text-black/60 leading-relaxed bg-black/[0.02] border border-black/5 rounded-xl p-3">
                            {{ $program->entry_requirements }}
                        </p>
                    </div>
                    @endif

                    {{-- Description --}}
                    @if($program->description)
                    <div>
                        <p class="text-xs font-semibold text-black/40 uppercase tracking-wide mb-1.5">About This Program</p>
                        <p class="text-sm text-black/60 leading-relaxed">{{ $program->description }}</p>
                    </div>
                    @endif

                    {{-- Career paths --}}
                    @if($program->careers->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold text-black/40 uppercase tracking-wide mb-2">Career Paths</p>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($program->careers->take(6) as $career)
                            <span class="px-2.5 py-1 bg-black/[0.03] text-black/60 text-xs rounded-lg border border-black/5">
                                {{ $career->title }}
                            </span>
                            @endforeach
                            @if($program->careers->count() > 6)
                            <span class="text-xs text-black/30 px-1.5">+{{ $program->careers->count() - 6 }} more</span>
                            @endif
                        </div>
                    </div>
                    @endif

                    {{-- Check My Match --}}
                    <div class="border-t border-black/5 pt-4">
                        @if($hasAcademicData && $reqSubs->isNotEmpty())
                        <div x-data="{ checked: false }">
                            <button @click="checked = !checked"
                                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-xs font-bold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span x-text="checked ? 'Hide Match Result' : 'Check If I Match'"></span>
                            </button>
                            <div x-show="checked" x-transition class="mt-3 space-y-2">
                                @php $details = $program->eligibility_details['subjects'] ?? []; @endphp
                                @if(!empty($details))
                                <div class="space-y-1.5 mb-2">
                                    @foreach($details as $d)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="{{ $d['met'] ? 'text-emerald-600' : 'text-red-500' }} font-bold text-sm leading-none">{{ $d['met'] ? '✓' : '✗' }}</span>
                                        <span class="font-medium text-black/70 flex-1">{{ $d['subject'] }}</span>
                                        <span class="text-black/40">{{ $d['has_data'] ? round($d['score']) . '%' : 'No grade' }}</span>
                                        <span class="text-black/35">Min. Grade {{ $d['required_grade'] }}</span>
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                                @if($eligible)
                                <div class="flex items-start gap-2 bg-green-50 border border-green-200 rounded-xl p-3">
                                    <svg class="w-4 h-4 text-green-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-green-800 mb-0.5">You meet the requirements for this program</p>
                                        <p class="text-xs text-green-700">Your teacher-uploaded grades satisfy the required subject minimum grades. Speak with your counsellor to confirm your full application eligibility.</p>
                                    </div>
                                </div>
                                @else
                                <div class="flex items-start gap-2 bg-red-50 border border-red-200 rounded-xl p-3">
                                    <svg class="w-4 h-4 text-red-400 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    <div>
                                        <p class="text-xs font-bold text-red-800 mb-0.5">You don't meet all requirements yet</p>
                                        <p class="text-xs text-red-700">One or more required subjects are below the minimum grade. Keep improving your grades and speak with your teacher or counsellor.</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @elseif($hasAcademicData && $reqSubs->isEmpty())
                        <div class="flex items-start gap-2 bg-blue-50/50 border border-blue-100 rounded-xl p-3">
                            <svg class="w-4 h-4 text-black/30 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-black/50">No specific subject requirements have been entered for this program. Contact your school counsellor to discuss entry criteria directly.</p>
                        </div>
                        @else
                        <p class="text-xs text-black/45 italic">Ask your teacher to upload your grades to check if you match this program.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    {{-- No results state --}}
    <div x-show="!anyMatch" x-cloak
         class="bg-white rounded-2xl border border-black/10 p-14 text-center">
        <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-blue-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <p class="font-semibold text-black/50 mb-1">No programs match your search</p>
        <p class="text-sm text-black/30 mb-4">Try different keywords or clear the filters</p>
        <button @click="search = ''; faculty = ''"
                class="px-5 py-2 text-sm font-semibold text-blue-600 border border-blue-200 rounded-xl hover:bg-blue-50 transition-colors">
            Clear Filters
        </button>
    </div>

</div>
@endsection
