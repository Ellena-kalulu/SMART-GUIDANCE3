@extends('layouts.dashboard')

@section('title', 'Student Career Profiles')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Counsellor</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Career Profiles</span>
    </nav>
@endsection

@section('content')
<div class="space-y-6">

    <div class="flex items-start justify-between">
        <div>
            <h1 class="text-2xl font-bold text-black/80">Student Career Profiles</h1>
            <p class="text-black/50 text-sm mt-1">Overview of every student's career pathway and match scores.</p>
        </div>
        <div class="flex gap-2">
            <input type="text" id="searchInput" placeholder="Search student…"
                   class="px-4 py-2.5 border border-black/15 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                   oninput="filterCards()">
        </div>
    </div>

    <!-- Cards grid -->
    <div id="profileGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($students as $student)
        @php
            $topCareers = $student->recommendations->take(3);
            $bestScore  = $student->recommendations->max('confidence_score');
            $hasProfile = $student->recommendations->isNotEmpty();
            $scoreColor = $bestScore >= 70 ? 'text-green-600' : ($bestScore >= 50 ? 'text-yellow-600' : 'text-red-500');
        @endphp
        <div class="profile-card bg-white rounded-2xl shadow-sm border border-black/10 p-5 flex flex-col gap-4"
             data-name="{{ strtolower($student->name) }}">

            <!-- Header -->
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 bg-blue-100 text-blue-700 rounded-xl flex items-center justify-center font-bold text-base shrink-0">
                    {{ strtoupper(substr($student->name, 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-black/80 truncate">{{ $student->name }}</p>
                    <p class="text-xs text-black/40">{{ $student->studentProfile->form_level ?? 'No form' }}</p>
                </div>
                @if($hasProfile)
                <span class="text-xl font-bold {{ $scoreColor }}">{{ round($bestScore) }}%</span>
                @else
                <span class="text-xs text-black/30 font-medium">No data</span>
                @endif
            </div>

            <!-- Career pathway -->
            @if($hasProfile)
            <div class="space-y-2">
                @foreach($topCareers as $rec)
                @php $pct = round($rec->confidence_score); @endphp
                <div>
                    <div class="flex items-center justify-between mb-0.5">
                        <p class="text-xs font-semibold text-black/70 truncate pr-2">{{ $rec->recommended->title ?? 'Career' }}</p>
                        <span class="text-xs font-bold text-black/60 shrink-0">{{ $pct }}%</span>
                    </div>
                    <div class="w-full h-1.5 bg-black/[0.04] rounded-full overflow-hidden">
                        <div class="h-full rounded-full {{ $pct >= 70 ? 'bg-blue-500' : ($pct >= 50 ? 'bg-yellow-400' : 'bg-red-400') }}"
                             style="width:{{ $pct }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-black/40 text-center py-3">Student hasn't completed the career assessment yet.</p>
            @endif

            <!-- Actions -->
            <div class="flex gap-2 pt-1 border-t border-black/5">
                <a href="{{ route('counsellor.student.detail', $student) }}"
                   class="flex-1 text-center py-2 text-xs font-semibold text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">
                    View Full Profile
                </a>
                <button onclick="openScheduleFor({{ $student->id }}, '{{ addslashes($student->name) }}')"
                        class="flex-1 text-center py-2 text-xs font-semibold text-black/50 hover:bg-black/5 rounded-lg transition-colors">
                    Schedule Session
                </button>
            </div>
        </div>
        @empty
        <div class="col-span-3 py-16 text-center text-black/40 text-sm">No students found.</div>
        @endforelse
    </div>
</div>

<!-- Quick Schedule Modal -->
<div id="quickScheduleModal" class="hidden fixed inset-0 bg-black/40 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
        <div class="flex items-center justify-between px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80 text-lg">Schedule Session</h2>
            <button onclick="document.getElementById('quickScheduleModal').classList.add('hidden')"
                    class="text-black/40 hover:text-black/70 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form action="{{ route('counsellor.sessions.store') }}" method="POST" class="p-6 space-y-4">
            @csrf
            <input type="hidden" name="student_id" id="modalStudentId">

            <div class="flex items-center gap-3 bg-blue-50 rounded-xl px-4 py-3">
                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <p class="text-sm font-semibold text-blue-700" id="modalStudentName">Student</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Date & Time</label>
                <input type="datetime-local" name="scheduled_at" required
                       min="{{ now()->addMinutes(5)->format('Y-m-d\TH:i') }}"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Venue</label>
                <input type="text" name="venue" required placeholder="e.g. Guidance Office, Room 12…"
                       class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm
                              focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <div>
                <label class="block text-sm font-semibold text-black/70 mb-1.5">Message <span class="font-normal text-black/40">(optional)</span></label>
                <textarea name="message" rows="2" placeholder="What should the student prepare?"
                          class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm resize-none
                                 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button"
                        onclick="document.getElementById('quickScheduleModal').classList.add('hidden')"
                        class="flex-1 px-5 py-2.5 border border-black/15 rounded-xl text-sm font-semibold text-black/60 hover:bg-black/5 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold text-sm transition-colors">
                    Schedule
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function filterCards() {
    const q = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.profile-card').forEach(card => {
        card.style.display = card.dataset.name.includes(q) ? '' : 'none';
    });
}

function openScheduleFor(id, name) {
    document.getElementById('modalStudentId').value = id;
    document.getElementById('modalStudentName').textContent = name;
    document.getElementById('quickScheduleModal').classList.remove('hidden');
}
</script>
@endpush
@endsection
