@extends('layouts.dashboard')
@section('title', 'Students Needing Attention')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Students Needing Attention</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-black/80">Students Needing Attention</h1>
            <p class="text-black/50 text-sm mt-1">{{ count($students) }} student(s) require intervention or follow-up.</p>
        </div>
        <a href="{{ route('teacher.reports.attention-list') }}?format=pdf"
           class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-semibold hover:bg-blue-700 transition-colors">
            Export PDF
        </a>
    </div>

    @if(empty($students))
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-green-600 font-semibold">All students are on track!</p>
        <p class="text-black/40 text-sm mt-1">No students currently flagged for attention.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">#</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Student</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Form</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Stream</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Assessment</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Match Score</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Reason</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($students as $i => $s)
                <tr class="hover:bg-black/[0.01] transition-colors">
                    <td class="px-5 py-3 text-black/40">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $s['name'] }}</td>
                    <td class="px-5 py-3 text-black/60">{{ $s['form'] }}</td>
                    <td class="px-5 py-3 text-black/60">{{ $s['stream'] }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                            {{ $s['has_assessment'] ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-600' }}">
                            {{ $s['has_assessment'] ? 'Done' : 'Pending' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <span class="font-semibold {{ $s['match_score'] >= 60 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $s['match_score'] }}%
                        </span>
                    </td>
                    <td class="px-5 py-3 text-black/50 text-xs">{{ $s['reason'] }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('teacher.student.detail', $s['id']) }}"
                           class="text-blue-600 hover:underline text-xs font-semibold">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
