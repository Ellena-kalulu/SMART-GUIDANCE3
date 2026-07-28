@extends('layouts.dashboard')
@section('title', 'Subject Performance')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.reports.index') }}" class="text-black/50 hover:text-blue-600">Reports</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Subject Performance</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-black/80">Subject Performance Analysis</h1>
        <p class="text-black/50 text-sm mt-1">Average, highest, and lowest scores per subject.</p>
    </div>

    @if($subjectData->isEmpty())
    <div class="bg-white rounded-2xl p-12 border border-black/10 text-center">
        <p class="text-black/40">No academic result data available yet.</p>
    </div>
    @else
    <div class="bg-white rounded-2xl border border-black/10 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-black/[0.03] border-b border-black/10">
                    <th class="px-5 py-3 text-left font-semibold text-black/60">#</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Subject</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Avg Score</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Highest</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Lowest</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Students</th>
                    <th class="px-5 py-3 text-left font-semibold text-black/60">Distribution</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-black/5">
                @foreach($subjectData as $i => $sub)
                @php $c = $sub['avg_score'] >= 70 ? 'green' : ($sub['avg_score'] >= 50 ? 'yellow' : 'red'); @endphp
                <tr class="hover:bg-black/[0.01]">
                    <td class="px-5 py-3 text-black/40">{{ $i + 1 }}</td>
                    <td class="px-5 py-3 font-semibold text-black/80">{{ $sub['name'] }}</td>
                    <td class="px-5 py-3 font-bold text-{{ $c }}-600">{{ $sub['avg_score'] }}%</td>
                    <td class="px-5 py-3 text-green-600">{{ $sub['highest_score'] }}%</td>
                    <td class="px-5 py-3 text-red-500">{{ $sub['lowest_score'] }}%</td>
                    <td class="px-5 py-3 text-black/60">{{ $sub['students_count'] }}</td>
                    <td class="px-5 py-3 w-32">
                        <div class="h-2 bg-black/5 rounded-full overflow-hidden">
                            <div class="h-full bg-{{ $c }}-500 rounded-full" style="width: {{ $sub['avg_score'] }}%"></div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>
@endsection
