@extends('layouts.dashboard')
@section('title', 'Intervention Tracking')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Intervention Tracking</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Intervention Tracking</h1>
        <p class="text-black/50 text-sm mt-1">Counselling sessions and intervention activities logged in the system.</p>
    </div>

    @if($interventions->isEmpty())
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-black/40">No counselling sessions recorded yet.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">#</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">User</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Action</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($interventions as $i => $log)
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 text-black/40">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $log->user?->name ?? 'System' }}</td>
                    <td class="px-5 py-3 text-black/60">{{ str_replace('_', ' ', $log->action) }}</td>
                    <td class="px-5 py-3 text-black/50">{{ $log->created_at->format('d M Y, H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
