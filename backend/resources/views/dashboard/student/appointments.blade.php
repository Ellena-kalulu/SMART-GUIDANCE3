@extends('layouts.dashboard')

@section('title', 'My Counsellor Sessions')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Counsellor Sessions</span>
</nav>
@endsection

@section('content')
@php
    $upcomingCount = $appointments->whereIn('status', ['pending_confirmation', 'scheduled'])->count();
    $completedCount = $appointments->where('status', 'completed')->count();
    $avatarPalettes = [
        ['from-blue-500', 'to-blue-600', 'ring-blue-200'],
        ['from-violet-500', 'to-purple-600', 'ring-violet-200'],
        ['from-cyan-500', 'to-teal-600', 'ring-cyan-200'],
        ['from-rose-500', 'to-pink-600', 'ring-rose-200'],
        ['from-amber-500', 'to-orange-600', 'ring-amber-200'],
        ['from-emerald-500', 'to-green-600', 'ring-emerald-200'],
    ];
@endphp

<div x-data="{ bookingOpen: false }">
<div class="space-y-8">

    {{-- Hero Header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-700 via-blue-600 to-blue-700 text-white shadow-xl shadow-blue-900/20">
        <div class="absolute inset-0 opacity-30"
             style="background-image: radial-gradient(circle at 20% 80%, #60a5fa 0%, transparent 50%), radial-gradient(circle at 90% 10%, #60a5fa 0%, transparent 40%);"></div>
        <div class="absolute -right-8 -top-8 w-64 h-64 rounded-full bg-white/5 blur-2xl"></div>
        <div class="absolute -left-12 bottom-0 w-48 h-48 rounded-full bg-blue-400/20 blur-3xl"></div>

        <div class="relative px-6 py-8 md:px-10 md:py-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
            <div class="max-w-xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 border border-white/20 text-blue-100 text-xs font-semibold uppercase tracking-wider mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Career Guidance
                </div>
                <h1 class="text-3xl md:text-4xl font-black tracking-tight mb-3">Counsellor Sessions</h1>
                <p class="text-blue-100/90 text-sm md:text-base leading-relaxed mb-6">
                    Book a one-on-one session with a school counsellor to discuss your career path, subject choices, and university plans.
                </p>
                <div class="flex flex-wrap items-center gap-3">
                    <button @click="bookingOpen = true"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-white text-blue-700 hover:bg-blue-50 rounded-xl font-bold text-sm shadow-lg shadow-black/10 transition-all hover:scale-[1.02] active:scale-[0.98]">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Book New Session
                    </button>
                    @if($upcomingCount > 0)
                    <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white/10 border border-white/20 text-sm font-medium text-blue-50">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $upcomingCount }} upcoming
                    </span>
                    @endif
                </div>
            </div>

            {{-- Illustration --}}
            <div class="hidden lg:block relative shrink-0">
                <div class="w-72 h-56 relative">
                    <div class="absolute inset-0 bg-white/10 backdrop-blur-sm rounded-2xl border border-white/20 shadow-2xl rotate-3"></div>
                    <div class="absolute inset-0 bg-white rounded-2xl border border-white/30 shadow-2xl -rotate-2 p-5">
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10px] font-bold text-black/40 uppercase tracking-wide">This Month</p>
                                    <p class="text-sm font-bold text-black/80">{{ now()->format('F Y') }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 rounded-lg bg-emerald-100 text-emerald-700 text-[10px] font-bold">Active</span>
                        </div>
                        <div class="space-y-2">
                            @for($i = 0; $i < 3; $i++)
                            <div class="flex items-center gap-2 p-2 rounded-lg {{ $i === 0 ? 'bg-blue-50 border border-blue-100' : 'bg-black/[0.03]' }}">
                                <div class="w-2 h-2 rounded-full {{ $i === 0 ? 'bg-blue-500' : ($i === 1 ? 'bg-amber-400' : 'bg-emerald-400') }}"></div>
                                <div class="flex-1 h-2 rounded-full {{ $i === 0 ? 'bg-blue-200' : 'bg-black/10' }}"></div>
                            </div>
                            @endfor
                        </div>
                        <div class="mt-4 flex items-center justify-between text-[10px] text-black/40 font-medium">
                            <span>{{ $counsellors->count() }} counsellors</span>
                            <span>{{ $completedCount }} completed</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl px-5 py-4 text-sm font-medium shadow-sm">
        <div class="w-9 h-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        {{ session('success') }}
    </div>
    @endif

    {{-- Counsellors available --}}
    @if($counsellors->isNotEmpty())
    <section>
        <div class="flex items-end justify-between gap-4 mb-5">
            <div>
                <h2 class="text-lg font-bold text-black/90">Available Counsellors</h2>
                <p class="text-sm text-black/50 mt-0.5">Choose a counsellor and book a session that fits your schedule.</p>
            </div>
            <span class="hidden sm:inline-flex items-center px-3 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold border border-blue-100">
                {{ $counsellors->count() }} available
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($counsellors as $index => $counsellor)
            @php
                $palette = $avatarPalettes[$index % count($avatarPalettes)];
            @endphp
            <div class="group relative bg-white rounded-2xl border border-black/[0.08] p-5 shadow-sm hover:shadow-md hover:border-blue-200 transition-all duration-200 hover:-translate-y-0.5">
                <div class="flex items-start gap-4">
                    <div class="relative shrink-0">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-br {{ $palette[0] }} {{ $palette[1] }} flex items-center justify-center text-white font-bold text-lg shadow-lg ring-4 {{ $palette[2] }}">
                            {{ strtoupper(substr($counsellor->name, 0, 2)) }}
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white"></span>
                    </div>
                    <div class="flex-1 min-w-0 pt-0.5">
                        <p class="font-bold text-black/90 truncate">{{ $counsellor->name }}</p>
                        <p class="text-xs text-black/45 mt-0.5">School Counsellor</p>
                        <div class="flex items-center gap-1.5 mt-2">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-[11px] font-medium text-emerald-600">Accepting bookings</span>
                        </div>
                    </div>
                </div>
                <button @click="bookingOpen = true; $nextTick(() => document.getElementById('counsellorSelect').value = '{{ $counsellor->id }}')"
                        class="mt-4 w-full flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 text-sm font-semibold border border-blue-100 group-hover:bg-blue-600 group-hover:text-white group-hover:border-blue-600 transition-colors">
                    Book Session
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </button>
            </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- Appointments timeline --}}
    <section class="bg-white rounded-3xl border border-black/[0.08] shadow-sm overflow-hidden">
        <div class="px-6 py-5 md:px-8 border-b border-black/[0.06] bg-gradient-to-r from-blue-50/30 to-white">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="font-bold text-black/90 text-lg">My Appointments</h2>
                        <p class="text-xs text-black/45">Your session history and upcoming meetings</p>
                    </div>
                </div>
                @if($appointments->isNotEmpty())
                <span class="text-xs font-semibold text-black/40 bg-black/[0.04] px-3 py-1.5 rounded-full">
                    {{ $appointments->count() }} total
                </span>
                @endif
            </div>
        </div>

        @forelse($appointments as $appt)
        @php
            $statusConfig = match($appt->status) {
                'pending_confirmation' => ['Waiting for Confirmation', 'bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-400', 'ring-amber-100'],
                'scheduled'            => ['Confirmed', 'bg-blue-50 text-blue-700 border-blue-200', 'bg-blue-500', 'ring-blue-100'],
                'completed'            => ['Completed', 'bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500', 'ring-emerald-100'],
                'cancelled'            => ['Cancelled', 'bg-red-50 text-red-600 border-red-200', 'bg-red-400', 'ring-red-100'],
                default                => [ucfirst($appt->status), 'bg-gray-50 text-gray-600 border-gray-200', 'bg-gray-400', 'ring-gray-100'],
            };
            $typeIcon = match($appt->appointment_type) {
                'online' => '💻',
                'phone'  => '📞',
                default  => '🏫',
            };
        @endphp
        <div class="relative px-6 md:px-8 py-6 border-b border-black/[0.04] last:border-0 hover:bg-blue-50/30 transition-colors">
            {{-- Timeline connector --}}
            @if(!$loop->last)
            <div class="absolute left-[2.65rem] md:left-[3.15rem] top-[4.5rem] bottom-0 w-0.5 bg-gradient-to-b from-blue-200 to-transparent"></div>
            @endif

            <div class="flex items-start gap-4 md:gap-6">
                {{-- Timeline node + date --}}
                <div class="relative shrink-0 flex flex-col items-center">
                    <div class="w-4 h-4 rounded-full {{ $statusConfig[2] }} ring-4 {{ $statusConfig[3] }} mb-3 z-10"></div>
                    <div class="w-16 md:w-[4.5rem] text-center bg-gradient-to-b from-blue-50 to-white rounded-2xl py-3 border border-blue-100 shadow-sm">
                        <p class="text-[10px] font-bold text-blue-400 uppercase tracking-wide">{{ $appt->scheduled_at->format('M') }}</p>
                        <p class="text-2xl font-black text-blue-700 leading-none my-0.5">{{ $appt->scheduled_at->format('d') }}</p>
                        <p class="text-[10px] text-blue-400 font-medium">{{ $appt->scheduled_at->format('D') }}</p>
                    </div>
                </div>

                {{-- Content card --}}
                <div class="flex-1 min-w-0 bg-white rounded-2xl border border-black/[0.06] p-4 md:p-5 shadow-sm">
                    <div class="flex items-start justify-between gap-3 flex-wrap">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-black/[0.04] text-xs font-semibold text-black/70">
                                    {{ $typeIcon }} {{ $appt->appointmentTypeLabel() }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $statusConfig[1] }}">
                                    {{ $statusConfig[0] }}
                                </span>
                            </div>
                            <h3 class="font-bold text-black/90 text-base">
                                Session with {{ $appt->counsellor->name ?? 'Counsellor' }}
                            </h3>
                            <p class="text-sm text-black/50 mt-1 flex items-center gap-1.5 flex-wrap">
                                <svg class="w-4 h-4 text-black/30 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                {{ $appt->scheduled_at->format('l, d M Y') }}
                                @if($appt->venue)
                                <span class="text-black/30">&bull;</span>
                                <span class="inline-flex items-center gap-1 font-medium text-black/60">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $appt->venue }}
                                </span>
                                @endif
                            </p>
                        </div>

                        @if(in_array($appt->status, ['pending_confirmation', 'scheduled']))
                        <form action="{{ route('student.appointments.cancel', $appt) }}" method="POST" class="shrink-0">
                            @csrf @method('PATCH')
                            <button type="submit"
                                    onclick="return confirm('Cancel this appointment?')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-red-600 bg-red-50 border border-red-100 hover:bg-red-100 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Cancel
                            </button>
                        </form>
                        @endif
                    </div>

                    @if($appt->student_reason)
                    <div class="mt-4 p-3 rounded-xl bg-blue-50/50 border border-blue-100">
                        <p class="text-[10px] font-bold text-black/35 uppercase tracking-wider mb-1">Your reason</p>
                        <p class="text-sm text-black/70 leading-relaxed">{{ $appt->student_reason }}</p>
                    </div>
                    @endif

                    @if($appt->counsellor_notes)
                    <div class="mt-3 p-4 bg-gradient-to-br from-blue-50 to-blue-50 rounded-xl border border-blue-100">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="text-xs font-bold text-blue-700 uppercase tracking-wide">Counsellor's Response</p>
                        </div>
                        <p class="text-sm text-black/70 leading-relaxed">{{ $appt->counsellor_notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="px-6 py-20 text-center">
            <div class="relative w-20 h-20 mx-auto mb-6">
                <div class="absolute inset-0 bg-blue-100 rounded-3xl rotate-6"></div>
                <div class="relative w-full h-full bg-gradient-to-br from-blue-50 to-blue-100 rounded-3xl flex items-center justify-center border border-blue-100">
                    <svg class="w-9 h-9 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <h3 class="font-bold text-black/70 text-lg mb-1">No appointments yet</h3>
            <p class="text-black/40 text-sm mb-5 max-w-sm mx-auto">Schedule your first counsellor session to get personalised career guidance.</p>
            <button @click="bookingOpen = true"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-blue-600/20 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Book your first session
            </button>
        </div>
        @endforelse
    </section>

    {{-- How it works --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-50/60 via-blue-50/40 to-blue-50 border border-blue-100/80 p-6 md:p-8">
        <div class="absolute top-0 right-0 w-40 h-40 bg-blue-200/20 rounded-full blur-3xl"></div>
        <h3 class="font-bold text-black/80 text-base mb-6 relative">How Counsellor Sessions Work</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative">
            @foreach([
                ['1', 'Book', 'Choose a counsellor, pick a date, and describe what you need help with.', 'from-blue-500 to-blue-600'],
                ['2', 'Confirm', 'The counsellor reviews your request and confirms the appointment with a time and venue.', 'from-violet-500 to-purple-600'],
                ['3', 'Meet', 'Attend the session and get personalised career guidance tailored to you.', 'from-emerald-500 to-teal-600'],
            ] as [$step, $title, $desc, $gradient])
            <div class="flex gap-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $gradient }} flex items-center justify-center text-white font-black text-sm shrink-0 shadow-md">
                    {{ $step }}
                </div>
                <div>
                    <p class="font-bold text-black/80 text-sm mb-1">{{ $title }}</p>
                    <p class="text-sm text-black/55 leading-relaxed">{{ $desc }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>{{-- end space-y-8 --}}

{{-- ── BOOKING MODAL ── --}}
<div x-show="bookingOpen"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     @click.self="bookingOpen = false"
     class="fixed inset-0 bg-black/60 backdrop-blur-md z-50 flex items-center justify-center p-4"
     style="display:none">
    <div x-show="bookingOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         class="bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden max-h-[90vh] flex flex-col">

        {{-- Modal header --}}
        <div class="relative px-6 py-5 bg-gradient-to-br from-blue-600 to-blue-700 text-white shrink-0">
            <div class="absolute inset-0 opacity-20"
                 style="background-image: radial-gradient(circle at 80% 20%, #fff 0%, transparent 50%);"></div>
            <div class="relative flex items-start justify-between gap-4">
                <div>
                    <p class="text-blue-200 text-xs font-semibold uppercase tracking-wider mb-1">New Request</p>
                    <h2 class="font-bold text-xl">Book a Counsellor Session</h2>
                    <p class="text-blue-100/80 text-sm mt-1">Fill in the details below and your counsellor will confirm.</p>
                </div>
                <button @click="bookingOpen = false"
                        class="w-9 h-9 flex items-center justify-center rounded-xl bg-white/15 hover:bg-white/25 text-white transition-colors shrink-0">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <form action="{{ route('student.appointments.store') }}" method="POST" class="p-6 space-y-5 overflow-y-auto">
            @csrf

            {{-- Counsellor selection --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-black/70 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">1</span>
                    Choose a Counsellor
                </label>
                <select id="counsellorSelect" name="counsellor_id" required
                        class="w-full px-4 py-3 border border-black/10 rounded-xl text-sm text-black/70 bg-blue-50/50
                               focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors">
                    <option value="">-- Select a counsellor --</option>
                    @foreach($counsellors as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Appointment type --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-black/70 mb-3">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">2</span>
                    Type of Meeting
                </label>
                <div class="grid grid-cols-3 gap-3">
                    @foreach([
                        ['in_person', '🏫', 'In Person', 'Meet at the guidance office'],
                        ['online',    '💻', 'Online',    'Video call (Teams/Zoom)'],
                        ['phone',     '📞', 'Phone Call', 'Telephone conversation'],
                    ] as [$val, $icon, $label, $desc])
                    <label class="cursor-pointer">
                        <input type="radio" name="appointment_type" value="{{ $val }}"
                               class="sr-only peer" {{ $val === 'in_person' ? 'checked' : '' }}>
                        <div class="p-3 rounded-2xl border-2 border-black/10 text-center transition-all
                                    peer-checked:border-blue-600 peer-checked:bg-blue-50 peer-checked:shadow-md peer-checked:shadow-blue-100
                                    hover:border-blue-300 hover:bg-blue-50/50">
                            <div class="text-2xl mb-1.5">{{ $icon }}</div>
                            <p class="text-xs font-bold text-black/80">{{ $label }}</p>
                            <p class="text-[10px] text-black/40 mt-0.5 leading-tight">{{ $desc }}</p>
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            {{-- Preferred date --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-black/70 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">3</span>
                    Preferred Date
                </label>
                <input type="date" name="preferred_date" required
                       min="{{ now()->addDay()->format('Y-m-d') }}"
                       class="w-full px-4 py-3 border border-black/10 rounded-xl text-sm bg-blue-50/50
                              focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors">
                <p class="text-xs text-black/40 mt-1.5 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    The counsellor will confirm the exact time.
                </p>
            </div>

            {{-- Reason / message --}}
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-black/70 mb-2">
                    <span class="w-6 h-6 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs font-bold">4</span>
                    What do you need help with?
                </label>
                <textarea name="student_reason" required rows="4"
                          placeholder="Describe what you would like to discuss — e.g. choosing between careers, understanding your recommendations, subject selection for Form 3, university applications…"
                          class="w-full px-4 py-3 border border-black/10 rounded-xl text-sm resize-none bg-blue-50/50
                                 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:bg-white transition-colors"></textarea>
                <p class="text-xs text-black/40 mt-1.5">Be as specific as possible so the counsellor can prepare for your session.</p>
            </div>

            <div class="flex gap-3 pt-2 border-t border-black/[0.06]">
                <button type="button" @click="bookingOpen = false"
                        class="flex-1 px-5 py-3 border border-black/10 rounded-xl text-sm font-semibold text-black/60 hover:bg-black/[0.03] transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-3 bg-gradient-to-r from-blue-600 to-blue-600 hover:from-blue-700 hover:to-blue-700 text-white rounded-xl font-semibold text-sm shadow-lg shadow-blue-600/25 transition-all">
                    Send Request
                </button>
            </div>
        </form>
    </div>
</div>
</div>{{-- end x-data --}}
@endsection
