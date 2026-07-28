@extends('layouts.dashboard')
@section('title', 'Counselling Session Log')

@section('sidebar')
    @include('dashboard.counsellor.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('counsellor.dashboard') }}" class="text-black/50 hover:text-blue-600">Counsellor</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('counsellor.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Session Log</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-2xl p-6 text-white">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-1">Report #26</p>
                <h1 class="text-2xl font-bold">Counselling Session Log</h1>
                <p class="text-blue-100 text-sm mt-1">Complete history of counselling sessions, notes, and outcomes</p>
            </div>
            <div class="flex items-center gap-3">
                @include('counsellor.reports.partials.export-buttons', ['routeName' => 'counsellor.reports.session-log'])
            </div>
        </div>
    </div>

    {{-- Sessions Table --}}
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10">
            <h2 class="font-bold text-black/80">Session History</h2>
            <p class="text-xs text-black/50 mt-0.5">{{ $sessions->total() }} total sessions recorded</p>
        </div>
        <div class="divide-y divide-black/5">
            @forelse($sessions as $session)
            @php $notes = $session->meta['notes'] ?? $session->description ?? 'No notes recorded'; @endphp
            <div class="px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-start gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-semibold text-sm shrink-0">
                            {{ strtoupper(substr($session->user->name ?? '?', 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="font-medium text-black/80">{{ $session->user->name ?? 'Unknown Student' }}</p>
                            <p class="text-sm text-black/60 mt-1">{{ $notes }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-black/40 shrink-0">{{ $session->created_at->format('d M Y, H:i') }}</p>
                </div>
            </div>
            @empty
            <div class="px-6 py-12 text-center text-black/40 text-sm">
                No counselling sessions recorded yet.
            </div>
            @endforelse
        </div>
        @if($sessions->hasPages())
        <div class="px-6 py-4 border-t border-black/10">
            {{ $sessions->links() }}
        </div>
        @endif
    </div>

    <p class="text-xs text-black/40 text-right">Generated {{ now()->format('d M Y, H:i') }} by {{ auth()->user()->name }}</p>
</div>
@endsection
