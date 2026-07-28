@extends('layouts.dashboard')

@section('title', 'Student Detail')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('teacher.students') }}" class="text-black/50 hover:text-blue-600">Students</a>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">{{ $student->name }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    @if(session('success'))
    <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-3 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    <!-- Attention Flags -->
    @if(count($flags) > 0)
    <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 flex items-start gap-3">
        <svg class="w-5 h-5 text-red-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="font-semibold text-red-700 text-sm mb-1">This student requires attention</p>
            <ul class="space-y-0.5">
                @foreach($flags as $flag)
                <li class="text-sm text-red-600">· {{ $flag }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <!-- Profile Header -->
    <div class="bg-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative flex items-center gap-5">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center
                        text-white font-bold text-2xl shrink-0">
                {{ strtoupper(substr($student->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <p class="text-white text-xs font-semibold uppercase tracking-widest mb-1">Student Profile</p>
                <h1 class="text-2xl font-bold mb-1">{{ $student->name }}</h1>
                <p class="text-white text-sm">{{ $student->email }}
                    @if($student->studentProfile)
                        &nbsp;·&nbsp; {{ $student->studentProfile->form_level }}
                        @if($student->studentProfile->stream)
                            &nbsp;·&nbsp; {{ $student->studentProfile->stream }} Stream
                        @endif
                    @endif
                </p>
                <a href="{{ route('teacher.student.report', $student) }}"
                   class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 bg-white/15 hover:bg-white/25
                          text-white text-xs font-semibold rounded-lg transition-colors border border-white/20">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    </svg>
                    Download Report Card (PDF)
                </a>
            </div>
            <div class="text-right shrink-0">
                @php $topCareer = $careers->sortByDesc('confidence_score')->first(); @endphp
                @if($topCareer)
                <p class="text-blue-300 text-xs mb-1">Top Career Match</p>
                <p class="text-white font-bold text-lg">{{ $topCareer->recommended->title ?? '—' }}</p>
                <p class="text-blue-200 text-sm">{{ round($topCareer->confidence_score) }}% confidence</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $completedAttempts = $assessments->where('status','completed')->count();
            $careerRecs = $careers->count();
            $uniRecs    = $universities->count();
            $avgScore   = $student->academicResults->avg('score');
        @endphp
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $completedAttempts }}</p>
            <p class="text-xs text-black/50 mt-1">Assessments Done</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $careerRecs }}</p>
            <p class="text-xs text-black/50 mt-1">Career Matches</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $uniRecs }}</p>
            <p class="text-xs text-black/50 mt-1">University Options</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            @php $scoreColor = $avgScore >= 70 ? 'text-green-600' : ($avgScore >= 50 ? 'text-yellow-600' : 'text-red-600'); @endphp
            <p class="text-2xl font-bold {{ $avgScore ? $scoreColor : 'text-black/40' }}">
                {{ $avgScore ? round($avgScore) . '%' : '—' }}
            </p>
            <p class="text-xs text-black/50 mt-1">Avg Academic Score</p>
        </div>
    </div>

    @if(!empty($recommendationHistory))
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-6">
        <h2 class="font-bold text-black/80 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
            Recommendation History
        </h2>
        <p class="text-xs text-black/40 mb-5">Track how this student's subject combination recommendations evolved across assessments.</p>
        <div class="space-y-3">
            @foreach($recommendationHistory as $entry)
            <div class="flex items-center justify-between gap-4 p-4 rounded-xl bg-gradient-to-r from-blue-50/80 to-blue-50/50 border border-blue-100">
                <div>
                    <p class="font-semibold text-black/80 text-sm">{{ $entry['assessment'] }}</p>
                    <p class="text-xs text-black/45">{{ $entry['date'] ?? '—' }}</p>
                </div>
                <div class="text-right">
                    <p class="font-bold text-blue-700">{{ $entry['combination'] }}</p>
                    <p class="text-sm text-black/50">{{ round($entry['score']) }}% suitability</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Career Recommendations + Academic Results -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Career Recommendations -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Career Recommendations
            </h2>
            @forelse($careers->sortByDesc('confidence_score')->take(5) as $rec)
            <div class="py-2.5 border-b border-black/5 last:border-0">
                <div class="flex items-center justify-between mb-1">
                    <div>
                        <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->title ?? 'Career' }}</p>
                        <p class="text-xs text-black/50">{{ $rec->recommended->category ?? '' }}</p>
                    </div>
                    <span class="text-sm font-bold text-blue-600">{{ round($rec->confidence_score) }}%</span>
                </div>
                @if($rec->recommended && $rec->recommended->subjects && $rec->recommended->subjects->isNotEmpty())
                <div class="flex flex-wrap gap-1 mt-1">
                    @foreach($rec->recommended->subjects->take(4) as $subj)
                    <span class="px-1.5 py-0.5 bg-blue-50 text-blue-600 text-xs rounded font-medium">{{ $subj->name }}</span>
                    @endforeach
                </div>
                @endif
            </div>
            @empty
            <p class="text-sm text-black/40 py-4 text-center">No career recommendations yet.</p>
            @endforelse
        </div>

        <!-- Academic Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                Academic Results
            </h2>
            @forelse($student->academicResults->sortByDesc('term')->take(8) as $result)
            @php $s = $result->score; $color = $s >= 70 ? 'green' : ($s >= 50 ? 'orange' : 'red'); @endphp
            <div class="flex items-center gap-3 py-2 border-b border-black/5 last:border-0">
                <div class="flex-1">
                    <p class="text-sm font-medium text-black/80">{{ $result->subject->name ?? 'Subject' }}</p>
                    <p class="text-xs text-black/40">Term {{ $result->term }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-16 h-1.5 bg-black/[0.03] rounded-full overflow-hidden">
                        <div class="h-full bg-{{ $color }}-500 rounded-full" style="width:{{ $s }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-{{ $color }}-600 w-8 text-right">{{ $s }}%</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-black/40 py-4 text-center">No academic results recorded.</p>
            @endforelse
        </div>
    </div>

    <!-- Career vs Academic Alignment -->
    @if($careers->isNotEmpty() && $student->academicResults->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
        <h2 class="font-bold text-black/80 mb-1 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
            </svg>
            Career vs Academic Alignment
        </h2>
        <p class="text-xs text-black/40 mb-4">How this student's subject performance aligns with their top career recommendations.</p>

        @php $academicBySubject = $student->academicResults->keyBy(fn($r) => $r->subject?->name); @endphp

        <div class="space-y-4">
            @foreach($careers->sortByDesc('confidence_score')->take(3) as $rec)
            @php
                $relatedSubjects = $rec->recommended?->subjects ?? collect();
                $matchScores = $relatedSubjects->map(fn($subj) => $academicBySubject->get($subj->name)?->score)->filter();
                $alignScore  = $matchScores->isNotEmpty() ? round($matchScores->avg()) : null;
                $careerPct   = round($rec->confidence_score);
                $gap         = $alignScore !== null ? $alignScore - $careerPct : null;
            @endphp
            <div class="border border-black/8 rounded-xl p-4">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->title ?? 'Career' }}</p>
                        <p class="text-xs text-black/40">Career match: {{ $careerPct }}%</p>
                    </div>
                    @if($alignScore !== null)
                    <div class="text-right">
                        <p class="text-sm font-bold {{ $alignScore >= 70 ? 'text-green-600' : ($alignScore >= 50 ? 'text-yellow-600' : 'text-red-500') }}">
                            {{ $alignScore }}%
                        </p>
                        <p class="text-xs text-black/40">Subject avg</p>
                    </div>
                    @else
                    <span class="text-xs text-black/30">No matching subjects</span>
                    @endif
                </div>
                @if($relatedSubjects->isNotEmpty())
                <div class="space-y-1.5">
                    @foreach($relatedSubjects->take(4) as $subj)
                    @php $result = $academicBySubject->get($subj->name); $sc = $result?->score; @endphp
                    <div class="flex items-center gap-3">
                        <p class="text-xs text-black/60 w-28 truncate">{{ $subj->name }}</p>
                        <div class="flex-1 h-1.5 bg-black/[0.04] rounded-full overflow-hidden">
                            @if($sc !== null)
                            <div class="h-full rounded-full {{ $sc >= 70 ? 'bg-green-500' : ($sc >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                                 style="width:{{ $sc }}%"></div>
                            @endif
                        </div>
                        <span class="text-xs font-semibold w-10 text-right {{ $sc !== null ? ($sc >= 70 ? 'text-green-600' : ($sc >= 50 ? 'text-yellow-600' : 'text-red-500')) : 'text-black/30' }}">
                            {{ $sc !== null ? $sc . '%' : 'N/A' }}
                        </span>
                    </div>
                    @endforeach
                </div>
                @endif

                @if($gap !== null)
                <p class="text-xs mt-3 {{ $gap >= 0 ? 'text-green-600' : 'text-red-500' }}">
                    @if($gap >= 10)
                        Academic performance is strong for this career path.
                    @elseif($gap >= 0)
                        Academic performance is adequate for this career path.
                    @elseif($gap >= -15)
                        Academic performance needs some improvement for this path.
                    @else
                        Academic performance is significantly below the career match level.
                    @endif
                </p>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- University Programs -->
    @if($universities->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">University Programs</h2>
        </div>
        <div class="divide-y divide-black/5">
            @foreach($universities->sortByDesc('confidence_score') as $rec)
            @php $score = round($rec->confidence_score); @endphp
            <div class="px-6 py-4 flex items-center justify-between">
                <div>
                    <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->name ?? 'Programme' }}</p>
                    <p class="text-xs text-black/50">{{ $rec->recommended->university ?? '' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-16 h-1.5 bg-black/[0.04] rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $score >= 70 ? 'bg-green-500' : ($score >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                             style="width:{{ $score }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-blue-600 w-10 text-right">{{ $score }}%</span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Teacher Comments -->
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-6">
        <h2 class="font-bold text-black/80 mb-4">Leave a Comment</h2>
        <form action="{{ route('teacher.student.comment', $student) }}" method="POST">
            @csrf
            <textarea name="comment" rows="3" placeholder="Write an observation, note, or feedback about {{ $student->name }}…"
                      class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none
                             focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                      maxlength="1000"></textarea>
            <div class="flex justify-end mt-3">
                <button type="submit"
                        class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                    Post Comment
                </button>
            </div>
        </form>

        @if($comments->isNotEmpty())
        <div class="mt-5 pt-5 border-t border-black/5 space-y-3">
            <p class="text-xs font-semibold text-black/40 uppercase tracking-wider">Previous Comments</p>
            @foreach($comments as $c)
            <div class="bg-black/[0.02] rounded-xl px-4 py-3">
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-semibold text-black/60">{{ $c->teacher->name ?? 'Teacher' }}</p>
                    <p class="text-xs text-black/30">{{ $c->created_at->format('d M Y, H:i') }}</p>
                </div>
                <p class="text-sm text-black/70">{{ $c->comment }}</p>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- Back -->
    <div>
        <a href="{{ route('teacher.students') }}"
           class="inline-flex items-center gap-2 text-sm text-black/60 hover:text-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Students
        </a>
    </div>
</div>
@endsection
