@extends('layouts.dashboard')

@section('title', 'Student Detail')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <a href="{{ route('counsellor.students') }}" class="text-black/50 hover:text-blue-600">Students</a>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">{{ $student->name }}</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <!-- Profile Header -->
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-8 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
             style="background-image: radial-gradient(circle at 80% 20%, #3b82f6 0%, transparent 60%)"></div>
        <div class="relative flex items-center gap-5">
            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center text-white font-bold text-2xl shrink-0">
                {{ strtoupper(substr($student->name, 0, 2)) }}
            </div>
            <div class="flex-1">
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Student Profile</p>
                <h1 class="text-2xl font-bold">{{ $student->name }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ $student->email }}
                    @if($student->studentProfile)
                    &nbsp;·&nbsp; {{ $student->studentProfile->form_level }}
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php
            $careerCount = $student->recommendations->where('type','career')->count();
            $uniCount    = $student->recommendations->where('type','university_program')->where('confidence_score','>=',80)->count();
            $avgScore    = $student->academicResults->avg('score');
        @endphp
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $careerCount }}</p>
            <p class="text-xs text-black/50 mt-1">Career Matches</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $uniCount }}</p>
            <p class="text-xs text-black/50 mt-1">Uni Programs</p>
        </div>
        <div class="bg-white rounded-2xl p-4 shadow-sm border border-black/10 text-center">
            <p class="text-2xl font-bold text-blue-600">{{ $avgScore ? round($avgScore) . '%' : '—' }}</p>
            <p class="text-xs text-black/50 mt-1">Avg Score</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Career Recommendations -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4">Career Recommendations</h2>
            @forelse($student->recommendations->where('type','career')->sortByDesc('confidence_score')->take(6) as $rec)
            <div class="flex items-center gap-3 py-2.5 border-b border-black/5 last:border-0">
                <div class="flex-1">
                    <p class="font-semibold text-black/80 text-sm">{{ $rec->recommended->title ?? 'Career' }}</p>
                    <p class="text-xs text-black/50 line-clamp-1">{{ $rec->reason }}</p>
                </div>
                <div class="text-right shrink-0">
                    <span class="text-sm font-bold {{ $rec->confidence_score >= 70 ? 'text-blue-600' : 'text-blue-600' }}">
                        {{ round($rec->confidence_score) }}%
                    </span>
                </div>
            </div>
            @empty
            <p class="text-sm text-black/40 text-center py-6">No recommendations yet — student needs to complete assessment.</p>
            @endforelse
        </div>

        <!-- Academic Results -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-5">
            <h2 class="font-bold text-black/80 mb-4">Academic Results</h2>
            @forelse($student->academicResults->sortByDesc('term')->take(8) as $result)
            @php
                $s = $result->score;
                $barBg  = $s >= 70 ? 'bg-emerald-500'  : ($s >= 50 ? 'bg-blue-500'  : 'bg-rose-400');
                $txtCol = $s >= 70 ? 'text-emerald-700' : ($s >= 50 ? 'text-blue-600' : 'text-rose-500');
            @endphp
            <div class="flex items-center gap-3 py-2 border-b border-black/5 last:border-0">
                <p class="flex-1 text-sm font-medium text-black/80">{{ $result->subject->name ?? 'Subject' }}</p>
                <div class="flex items-center gap-2">
                    <div class="w-16 h-1.5 bg-black/10 rounded-full overflow-hidden">
                        <div class="h-full {{ $barBg }} rounded-full" style="width:{{ min($s,100) }}%"></div>
                    </div>
                    <span class="text-sm font-bold {{ $txtCol }} w-10 text-right">{{ $s }}%</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-black/40 text-center py-6">No results recorded.</p>
            @endforelse
        </div>
    </div>

    <!-- Counselling Note + Schedule Session -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        <!-- Add Note -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-6">
            <h2 class="font-bold text-black/80 mb-4">Add Counselling Note</h2>

            @if(session('success'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 rounded-xl px-4 py-2.5 text-sm font-medium mb-4">
                {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('counsellor.session.save') }}" method="POST">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->uuid }}">
                <textarea name="notes" rows="4" placeholder="Record guidance session notes for this student…"
                          class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                <div class="flex justify-end mt-3">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                        Send Note
                    </button>
                </div>
            </form>

            <!-- Past Notes -->
            @if($counsellingNotes->isNotEmpty())
            <div class="mt-5 pt-5 border-t border-black/5 space-y-3">
                <p class="text-xs font-semibold text-black/40 uppercase tracking-wider">Previous Notes</p>
                @foreach($counsellingNotes->take(5) as $log)
                <div class="bg-black/[0.02] rounded-xl px-4 py-3">
                    <p class="text-sm text-black/70">{{ $log->meta['notes'] ?? '' }}</p>
                    <p class="text-xs text-black/30 mt-1">{{ $log->created_at->format('d M Y, H:i') }}</p>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Schedule Session -->
        <div class="bg-white rounded-2xl shadow-sm border border-black/10 p-6">
            <h2 class="font-bold text-black/80 mb-4">Schedule Counselling Session</h2>
            <form action="{{ route('counsellor.sessions.store') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="student_id" value="{{ $student->id }}">

                <div>
                    <label class="block text-sm font-semibold text-black/70 mb-1.5">Date & Time</label>
                    <input type="datetime-local" name="scheduled_at" required
                           min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                           class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-black/70 mb-1.5">Venue</label>
                    <input type="text" name="venue" placeholder="e.g. Guidance Office, Room 12…"
                           class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm
                                  focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-black/70 mb-1.5">Message <span class="font-normal text-black/40">(optional)</span></label>
                    <textarea name="message" rows="3" placeholder="What should {{ $student->name }} prepare for this session?"
                              class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none
                                     focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                        Schedule Session
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div>
        <a href="{{ route('counsellor.students') }}"
           class="inline-flex items-center gap-2 text-sm text-black/60 hover:text-blue-600 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Students
        </a>
    </div>
</div>

@endsection
