@extends('layouts.dashboard')

@section('title', 'Teacher Reports')

@section('sidebar')
     @include('dashboard.teacher.partials.sidebar')

@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.dashboard') }}" class="text-black/50 hover:text-blue-600">Teacher</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Reports & Analytics</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 bg-blue-600 rounded-2xl p-6 text-white">
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold">Class Management Reports</h1>
        </div>
        <p class="text-blue-100 ml-14">Monitor student progress, track performance, and generate comprehensive reports</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-black/10">
            <div class="text-2xl mb-1">📊</div>
            <div class="text-xl font-bold text-black/80">{{ count($filterOptions['report_types']) }}</div>
            <div class="text-xs text-black/50">Report Types</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-black/10">
            <div class="text-2xl mb-1">👥</div>
            <div class="text-xl font-bold text-black/80">1,200+</div>
            <div class="text-xs text-black/50">Monthly Reports Generated</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-black/10">
            <div class="text-2xl mb-1">📈</div>
            <div class="text-xl font-bold text-black/80">PDF/CSV</div>
            <div class="text-xs text-black/50">Export Formats</div>
        </div>
        <div class="bg-white rounded-xl p-4 shadow-sm border border-black/10">
            <div class="text-2xl mb-1">⏰</div>
            <div class="text-xl font-bold text-black/80">Weekly</div>
            <div class="text-xs text-black/50">Auto-Summaries</div>
        </div>
    </div>

    <!-- Report Generation Form -->
    <div class="bg-white rounded-xl shadow-sm border border-black/15 overflow-hidden">
        <div class="px-6 py-4 border-b border-black/10 bg-gradient-to-r from-white to-white">
            <h2 class="font-semibold text-black/80">📋 Generate Custom Report</h2>
        </div>

        <form method="GET" action="{{ route('teacher.reports.index') }}" class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <label class="block text-sm font-medium text-black/70 mb-2">Report Type</label>
                    <select name="report_type" class="w-full px-4 py-2.5 border border-black/20 rounded-lg focus:ring-2 focus:ring-blue-500">
                        <option value="">Select Report Type</option>
                        @foreach($filterOptions['report_types'] as $type)
                            <option value="{{ $type['value'] }}" {{ ($reportType ?? '') == $type['value'] ? 'selected' : '' }}>
                                {{ $type['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @if($reportType && isset($filterOptions['report_types']))
                        @php $selected = collect($filterOptions['report_types'])->firstWhere('value', $reportType) @endphp
                        <p class="text-xs text-black/50 mt-1">{{ $selected['description'] ?? '' }}</p>
                    @endif
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-2">Form Level</label>
                    <select name="form_level" class="w-full px-4 py-2.5 border border-black/20 rounded-lg">
                        <option value="">All Forms</option>
                        @foreach($filterOptions['form_levels'] as $f)
                            <option value="{{ $f }}" {{ ($formLevel ?? '') == $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-2">Stream</label>
                    <select name="stream" class="w-full px-4 py-2.5 border border-black/20 rounded-lg">
                        <option value="">All Streams</option>
                        @foreach($filterOptions['streams'] as $s)
                            <option value="{{ $s }}" {{ ($stream ?? '') == $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-2">Date From</label>
                    <input type="date" name="date_from" value="{{ $dateFrom ?? '' }}" class="w-full px-4 py-2.5 border border-black/20 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-2">Date To</label>
                    <input type="date" name="date_to" value="{{ $dateTo ?? '' }}" class="w-full px-4 py-2.5 border border-black/20 rounded-lg">
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-2">Export Format</label>
                    <div class="flex gap-2">
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="format" value="html" class="hidden peer" {{ (!request('format') || request('format') === 'html') ? 'checked' : '' }}>
                            <div class="text-center py-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white border peer-checked:border-blue-600 text-black/60 transition-all">📄 HTML</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="format" value="pdf" class="hidden peer" {{ request('format') === 'pdf' ? 'checked' : '' }}>
                            <div class="text-center py-2 rounded-lg peer-checked:bg-blue-600 peer-checked:text-white border peer-checked:border-blue-600 text-black/60 transition-all">📑 PDF</div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input type="radio" name="format" value="csv" class="hidden peer" {{ request('format') === 'csv' ? 'checked' : '' }}>
                            <div class="text-center py-2 rounded-lg peer-checked:text-blue-600 peer-checked:text-white border peer-checked:text-blue-600 text-black/60 transition-all">📊 CSV</div>
                        </label>
                    </div>
                </div>
            </div>

            <div class="flex gap-3 mt-6 pt-4 border-t border-black/10">
                <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-blue-600 to-blue-600 text-white rounded-lg font-medium hover:from-blue-700 hover:bg-blue-700 transition-all">
                    Generate Report
                </button>
                <a href="{{ route('teacher.reports.index') }}" class="px-6 py-2.5 border border-black/20 text-black/70 rounded-lg hover:bg-white transition-all">
                    Reset Filters
                </a>
            </div>
        </form>
    </div>

    <!-- Quick Access Report Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <a href="{{ route('teacher.reports.bulk-progress') }}" class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl mb-1">📊</div>
                    <h3 class="font-semibold text-black/80">Bulk Progress Report</h3>
                    <p class="text-xs text-black/60 mt-1">Export all student progress data</p>
                </div>
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('teacher.reports.attention-list') }}" class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl mb-1">⚠️</div>
                    <h3 class="font-semibold text-black/80">Needs Attention</h3>
                    <p class="text-xs text-black/60 mt-1">Students requiring intervention</p>
                </div>
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>

        <a href="{{ route('teacher.reports.placement-prediction') }}" class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-4 hover:shadow-md transition-all">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-2xl mb-1">🎓</div>
                    <h3 class="font-semibold text-black/80">University Predictions</h3>
                    <p class="text-xs text-black/60 mt-1">Student placement forecasts</p>
                </div>
                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </div>
        </a>
    </div>
</div>
@endsection
