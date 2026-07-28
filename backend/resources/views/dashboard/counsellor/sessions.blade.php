@extends('layouts.dashboard')

@section('title', 'Counselling Sessions')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Counsellor</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Sessions</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6" x-data="{ scheduleOpen: false, confirmOpen: false, respondOpen: false, activeSession: {} }">

    {{-- Header Banner --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,#fff 0%,transparent 60%)"></div>
        <div class="relative flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="text-blue-300 text-xs font-semibold uppercase tracking-widest mb-1">Counsellor Portal</p>
                <h1 class="text-2xl md:text-3xl font-bold mb-1">Counselling Sessions</h1>
                <p class="text-white/70 text-sm">Manage appointments, confirm student requests, and track session history.</p>
            </div>
            <div class="flex items-center gap-3 flex-wrap">
                <div class="bg-white/10 rounded-xl px-4 py-2.5 text-center border border-white/15">
                    <p class="text-2xl font-bold">{{ $pending->count() }}</p>
                    <p class="text-xs text-blue-200">Pending</p>
                </div>
                <div class="bg-white/10 rounded-xl px-4 py-2.5 text-center border border-white/15">
                    <p class="text-2xl font-bold">{{ $upcoming->count() }}</p>
                    <p class="text-xs text-blue-200">Upcoming</p>
                </div>
                <div class="bg-white/10 rounded-xl px-4 py-2.5 text-center border border-white/15">
                    <p class="text-2xl font-bold">{{ $past->count() }}</p>
                    <p class="text-xs text-blue-200">Completed</p>
                </div>
                <button @click="scheduleOpen = true"
                        class="flex items-center gap-2 px-5 py-2.5 bg-white text-blue-700 hover:bg-blue-50 rounded-xl font-bold text-sm transition-colors shadow-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Schedule Session
                </button>
            </div>
        </div>
    </div>

    {{-- ── SESSION ANALYTICS CARDS ── --}}
    @php
        $totalSessions     = $pending->count() + $upcoming->count() + $past->count();
        $completedSessions = $past->where('status','completed')->count();
        $cancelledSessions = $past->where('status','cancelled')->count();
        $completionRate    = $totalSessions > 0 ? round(($completedSessions / $totalSessions) * 100) : 0;
        $onlineCount       = ($pending->where('appointment_type','online')->count() + $upcoming->where('appointment_type','online')->count());
        $inPersonCount     = ($pending->where('appointment_type','in_person')->count() + $upcoming->where('appointment_type','in_person')->count());
        $uniqueStudents    = collect()
            ->concat($past->pluck('student_id'))
            ->concat($upcoming->pluck('student_id'))
            ->filter()->unique()->count();
        $thisMonth         = ($past->concat($upcoming))->filter(fn($s) => $s->scheduled_at->isCurrentMonth())->count();
    @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Total Sessions',    $totalSessions,     'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'bg-blue-600'],
            ['Completion Rate',   $completionRate.'%','M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                           'bg-blue-500'],
            ['Students Reached',  $uniqueStudents,    'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197',          'bg-blue-700'],
            ['This Month',        $thisMonth,         'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',                                              'bg-blue-800'],
        ] as [$label, $val, $icon, $bg])
        <div class="bg-white rounded-2xl border border-blue-100 shadow-sm p-5 flex items-center gap-4">
            <div class="w-11 h-11 {{ $bg }} rounded-xl flex items-center justify-center shrink-0 shadow-sm">
                <svg class="w-5 h-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            <div>
                <p class="text-2xl font-black text-blue-700">{{ $val }}</p>
                <p class="text-xs text-black/50 font-medium">{{ $label }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ── QUICK ANALYTICS ROW ── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        {{-- Pending Urgency --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
            <p class="text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">Pending Requests</p>
            <p class="text-3xl font-black text-amber-800">{{ $pending->count() }}</p>
            <p class="text-xs text-amber-600 mt-1">Awaiting your response or confirmation</p>
            @if($pending->count() > 0)
            <p class="mt-2 text-xs text-amber-700 font-semibold">Oldest: {{ $pending->sortBy('created_at')->first()?->created_at->diffForHumans() }}</p>
            @endif
        </div>
        {{-- Session Types --}}
        <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5">
            <p class="text-xs font-bold text-blue-700 uppercase tracking-wide mb-3">Session Types (Active)</p>
            <div class="space-y-2">
                @foreach([
                    ['In Person', $inPersonCount, 'bg-blue-500'],
                    ['Online',    $onlineCount,   'bg-blue-400'],
                    ['Phone',     ($pending->where('appointment_type','phone')->count() + $upcoming->where('appointment_type','phone')->count()), 'bg-blue-300'],
                ] as [$type, $cnt, $color])
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 {{ $color }} rounded-full shrink-0"></div>
                    <span class="text-xs text-black/60 flex-1">{{ $type }}</span>
                    <span class="text-xs font-bold text-blue-700">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>
        </div>
        {{-- History --}}
        <div class="bg-white border border-black/10 rounded-2xl p-5">
            <p class="text-xs font-bold text-black/50 uppercase tracking-wide mb-3">Past Sessions</p>
            <div class="space-y-2">
                <div class="flex justify-between items-center">
                    <span class="text-xs text-black/60">Completed</span>
                    <span class="text-sm font-black text-blue-600">{{ $completedSessions }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-xs text-black/60">Cancelled</span>
                    <span class="text-sm font-black text-red-500">{{ $cancelledSessions }}</span>
                </div>
                <div class="pt-1 mt-1 border-t border-black/5 flex justify-between items-center">
                    <span class="text-xs font-semibold text-black/50">Completion Rate</span>
                    <span class="text-sm font-black text-blue-700">{{ $completionRate }}%</span>
                </div>
            </div>
        </div>
    </div>

    {{-- ── PENDING STUDENT REQUESTS ── --}}
    @if($pending->isNotEmpty())
    <div class="bg-amber-50 border border-amber-200 rounded-2xl overflow-hidden">
        <div class="px-6 py-4 border-b border-amber-200 flex items-center gap-3">
            <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <div>
                <h2 class="font-bold text-amber-800">Pending Student Requests <span class="ml-1 px-2 py-0.5 bg-amber-200 text-amber-800 text-xs rounded-full">{{ $pending->count() }}</span></h2>
                <p class="text-xs text-amber-600 mt-0.5">These students have requested a counselling session. Confirm or respond to each one.</p>
            </div>
        </div>

        <div class="divide-y divide-amber-100">
            @foreach($pending as $session)
            @php
                $typeIcon = match($session->appointment_type) {
                    'online' => '💻',
                    'phone'  => '📞',
                    default  => '🏫',
                };
            @endphp
            <div class="px-6 py-5 flex items-start gap-4 flex-wrap">
                {{-- Student avatar --}}
                <div class="w-10 h-10 rounded-full bg-amber-200 flex items-center justify-center text-amber-800 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($session->student->name ?? 'S', 0, 2)) }}
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <p class="font-semibold text-black/80">{{ $session->student->name ?? 'Unknown Student' }}</p>
                        <span class="px-2 py-0.5 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full border border-amber-200">Awaiting Confirmation</span>
                        <span class="text-xs text-black/40">{{ $typeIcon }} {{ $session->appointmentTypeLabel() }}</span>
                    </div>
                    <p class="text-xs text-black/50 mb-2">
                        Preferred date: <span class="font-medium text-black/70">{{ $session->scheduled_at->format('l, d M Y') }}</span>
                        &bull; Requested {{ $session->created_at->diffForHumans() }}
                    </p>
                    @if($session->student_reason)
                    <div class="p-3 bg-white rounded-xl border border-amber-200 mb-3">
                        <p class="text-xs font-semibold text-black/40 uppercase tracking-wide mb-1">Student's reason</p>
                        <p class="text-sm text-black/70">{{ $session->student_reason }}</p>
                    </div>
                    @endif
                    @if($session->counsellor_notes)
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="text-xs font-semibold text-blue-600 uppercase tracking-wide mb-1">Your response</p>
                        <p class="text-sm text-black/70">{{ $session->counsellor_notes }}</p>
                    </div>
                    @endif
                </div>

                <div class="flex flex-col gap-2 shrink-0">
                    {{-- Confirm (set time + venue) --}}
                    <button @click="activeSession = {{ json_encode(['id' => $session->id, 'student' => $session->student->name ?? 'Student', 'preferred' => $session->scheduled_at->format('Y-m-d'), 'type' => $session->appointmentTypeLabel()]) }}; confirmOpen = true"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold transition-colors whitespace-nowrap">
                        Confirm & Set Time
                    </button>
                    {{-- Respond without confirming --}}
                    <button @click="activeSession = {{ json_encode(['id' => $session->id, 'student' => $session->student->name ?? 'Student']) }}; respondOpen = true"
                            class="px-4 py-2 border border-black/15 text-black/60 hover:bg-black/5 rounded-lg text-xs font-semibold transition-colors whitespace-nowrap">
                        Send Message
                    </button>
                    {{-- Cancel --}}
                    <form action="{{ route('counsellor.sessions.cancel', $session) }}" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" onclick="return confirm('Decline this request?')"
                                class="w-full px-4 py-2 border border-red-200 text-red-600 hover:bg-red-50 rounded-lg text-xs font-semibold transition-colors">
                            Decline
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── UPCOMING CONFIRMED SESSIONS ── --}}
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">Upcoming Sessions <span class="ml-1 px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full">{{ $upcoming->count() }}</span></h2>
        </div>
        @forelse($upcoming as $session)
        @php
            $isPast = $session->scheduled_at->isPast();
            $typeIcon = match($session->appointment_type) { 'online' => '💻', 'phone' => '📞', default => '🏫' };
        @endphp
        <div class="flex items-start gap-4 px-6 py-5 border-b border-black/5 last:border-0">
            <div class="shrink-0 w-14 text-center bg-blue-50 rounded-xl py-2">
                <p class="text-xs font-semibold text-blue-400 uppercase">{{ $session->scheduled_at->format('M') }}</p>
                <p class="text-2xl font-bold text-blue-700 leading-none">{{ $session->scheduled_at->format('d') }}</p>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <p class="font-semibold text-black/80">
                        @if($session->isForAll())
                            All Students (General Session)
                        @else
                            {{ $session->student->name ?? 'Unknown Student' }}
                        @endif
                    </p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $isPast ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700' }}">
                        {{ $isPast ? 'Overdue' : 'Confirmed' }}
                    </span>
                    <span class="text-xs text-black/40">{{ $typeIcon }} {{ $session->appointmentTypeLabel() }}</span>
                </div>
                <p class="text-sm text-black/50 mt-0.5">
                    {{ $session->scheduled_at->format('l, d M Y \a\t H:i') }}
                    @if($session->venue)
                    &bull; <span class="font-medium text-black/60">{{ $session->venue }}</span>
                    @endif
                </p>
                @if($session->student_reason)
                <p class="text-xs text-black/50 mt-1 italic">Reason: {{ Str::limit($session->student_reason, 100) }}</p>
                @endif
                @if($session->counsellor_notes)
                <p class="text-xs text-blue-600 mt-1">Notes: {{ Str::limit($session->counsellor_notes, 80) }}</p>
                @endif
            </div>

            <div class="flex flex-col gap-2 shrink-0">
                <button @click="activeSession = {{ json_encode(['id' => $session->id, 'student' => $session->student->name ?? 'Student']) }}; respondOpen = true"
                        class="px-3 py-1.5 border border-black/15 text-black/60 hover:bg-black/5 rounded-lg text-xs font-semibold transition-colors">
                    Add Note
                </button>
                <form action="{{ route('counsellor.sessions.cancel', $session) }}" method="POST">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Cancel this session?')"
                            class="w-full text-xs text-red-500 hover:text-red-700 font-medium transition-colors py-1.5">
                        Cancel
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="px-6 py-10 text-center text-black/40 text-sm">
            No upcoming sessions. Click "Schedule Session" to add one.
        </div>
        @endforelse
    </div>

    {{-- ── PAST SESSIONS ── --}}
    @if($past->isNotEmpty())
    <div class="bg-white rounded-2xl shadow-sm border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80 text-base">Past Sessions</h2>
        </div>
        @foreach($past as $session)
        @php
            $statusColor = match($session->status) {
                'completed' => 'bg-green-100 text-green-700',
                'cancelled' => 'bg-red-100 text-red-700',
                default     => 'bg-gray-100 text-gray-600',
            };
        @endphp
        <div class="flex items-center gap-4 px-6 py-4 border-b border-black/5 last:border-0">
            <div class="shrink-0 w-12 text-center bg-black/[0.03] rounded-xl py-1.5">
                <p class="text-[10px] font-semibold text-black/40 uppercase">{{ $session->scheduled_at->format('M') }}</p>
                <p class="text-lg font-bold text-black/50 leading-none">{{ $session->scheduled_at->format('d') }}</p>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-black/70 truncate">
                    {{ $session->isForAll() ? 'All Students' : ($session->student->name ?? 'Unknown') }}
                </p>
                <p class="text-xs text-black/40">{{ $session->scheduled_at->format('d M Y') }}</p>
            </div>
            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusColor }}">
                {{ ucfirst($session->status) }}
            </span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── SCHEDULE SESSION MODAL (counsellor-initiated) ── --}}
    <div x-show="scheduleOpen" x-cloak
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="scheduleOpen = false"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div x-show="scheduleOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80 text-lg">Schedule Counselling Session</h2>
            <button @click="scheduleOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/10 text-black/50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form action="{{ route('counsellor.sessions.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Student</label>
                <select name="student_id"
                        class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm text-black/70 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All Students (General Session)</option>
                    @foreach($students as $s)
                    <option value="{{ $s->id }}">{{ $s->name }}@if($s->studentProfile) — {{ $s->studentProfile->form_level }}@endif</option>
                    @endforeach
                </select>
                <p class="text-xs text-black/40 mt-1">Leave blank for a general session open to all students.</p>
            </div>
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Date &amp; Time</label>
                <input type="datetime-local" name="scheduled_at" required
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Venue <span class="font-normal text-black/40">(optional for online/phone)</span></label>
                <input type="text" name="venue" placeholder="e.g. Guidance Office, Room 12, Library…"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Message / Agenda <span class="font-normal text-black/40">(optional)</span></label>
                <textarea name="message" rows="3" placeholder="What will the session cover?"
                          class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="scheduleOpen = false"
                        class="flex-1 px-5 py-2.5 border border-black/15 rounded-xl text-sm font-semibold text-black/60 hover:bg-black/5 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                    Schedule Session
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── CONFIRM SESSION MODAL (set exact time) ── --}}
<div x-show="confirmOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="confirmOpen = false"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div x-show="confirmOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-black/10">
            <div>
                <h2 class="font-bold text-black/80 text-lg">Confirm Appointment</h2>
                <p class="text-xs text-black/50 mt-0.5">for <span class="font-semibold" x-text="activeSession.student"></span></p>
            </div>
            <button @click="confirmOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/10 text-black/50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form :action="`/counsellor/sessions/${activeSession.id}/confirm`" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Confirmed Date &amp; Time</label>
                <input type="datetime-local" name="scheduled_at" required
                       :value="activeSession.preferred ? activeSession.preferred + 'T09:00' : ''"
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Venue <span class="font-normal text-black/40">(leave blank for online/phone)</span></label>
                <input type="text" name="venue" placeholder="e.g. Guidance Office, Room 12, or Online (Teams link)"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Message to Student <span class="font-normal text-black/40">(optional)</span></label>
                <textarea name="counsellor_notes" rows="3"
                          placeholder="Any instructions, preparation notes, or a link for the session…"
                          class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="confirmOpen = false"
                        class="flex-1 px-5 py-2.5 border border-black/15 rounded-xl text-sm font-semibold text-black/60 hover:bg-black/5 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                    Confirm Appointment
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── RESPOND/ADD NOTE MODAL ── --}}
<div x-show="respondOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="respondOpen = false"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div x-show="respondOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-black/10">
            <div>
                <h2 class="font-bold text-black/80 text-lg">Send Message</h2>
                <p class="text-xs text-black/50 mt-0.5">to <span class="font-semibold" x-text="activeSession.student"></span></p>
            </div>
            <button @click="respondOpen = false" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-black/10 text-black/50 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form :action="`/counsellor/sessions/${activeSession.id}/respond`" method="POST" class="p-6 space-y-4">
            @csrf @method('PATCH')
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Your Message / Notes</label>
                <textarea name="counsellor_notes" required rows="5"
                          placeholder="Write your response to the student — e.g. I have reviewed your request. Please come prepared with your subject choices. We will also review your career assessment results together…"
                          class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                <p class="text-xs text-black/40 mt-1">The student will see this message on their appointments page.</p>
            </div>
            <div class="flex gap-3 pt-1">
                <button type="button" @click="respondOpen = false"
                        class="flex-1 px-5 py-2.5 border border-black/15 rounded-xl text-sm font-semibold text-black/60 hover:bg-black/5 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                    Send Message
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
