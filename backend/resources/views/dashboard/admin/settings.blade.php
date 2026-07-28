@extends('layouts.dashboard')
@section('title', 'System Settings')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Settings</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-black text-black">System Settings</h1>
        <p class="text-black/50 text-sm mt-0.5">Configure the Smart Career & Subject Guidance Tool.</p>
    </div>

    {{-- General settings --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="font-bold text-black/80 mb-1">General</h2>
        <p class="text-sm text-black/50 mb-6">Basic system information displayed to users.</p>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">System / Site Name</label>
                    <input type="text" name="site_name"
                           value="{{ old('site_name', config('app.name', 'CareerGuide Luwinga')) }}"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('site_name')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">School Name</label>
                    <input type="text" name="school_name"
                           value="{{ old('school_name', 'Luwinga Secondary School') }}"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Contact Email</label>
                    <input type="email" name="contact_email"
                           value="{{ old('contact_email', config('mail.from.address')) }}"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('contact_email')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Support Phone</label>
                    <input type="text" name="support_phone"
                           value="{{ old('support_phone') }}"
                           placeholder="+265 XXX XXX XXX"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                Save General Settings
            </button>
        </form>
    </div>

    {{-- Assessment settings --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="font-bold text-black/80 mb-1">Assessment Configuration</h2>
        <p class="text-sm text-black/50 mb-6">Control how the career assessment behaves.</p>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Maximum Attempts per Student</label>
                    <select name="max_attempts"
                            class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach([1,2,3,5,10] as $n)
                        <option value="{{ $n }}" {{ $n === 3 ? 'selected' : '' }}>{{ $n }} attempt{{ $n !== 1 ? 's' : '' }}</option>
                        @endforeach
                        <option value="0">Unlimited</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Minimum Confidence Score to Show</label>
                    <select name="min_confidence"
                            class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                        @foreach([0, 30, 40, 50, 60] as $n)
                        <option value="{{ $n }}" {{ $n === 40 ? 'selected' : '' }}>{{ $n }}% and above</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="space-y-3">
                @foreach([
                    ['allow_retake',    'Allow students to retake the assessment'],
                    ['show_score',      'Show confidence score to students'],
                    ['require_profile', 'Require complete profile before assessment'],
                    ['notify_counsellor', 'Notify counsellor when student completes assessment'],
                ] as [$name, $label])
                <div class="flex items-center gap-3">
                    <input type="checkbox" name="{{ $name }}" id="{{ $name }}" value="1" checked
                           class="w-4 h-4 rounded border-black/20 text-blue-600 focus:ring-blue-500 cursor-pointer">
                    <label for="{{ $name }}" class="text-sm text-black/70 cursor-pointer">{{ $label }}</label>
                </div>
                @endforeach
            </div>

            <button type="submit"
                    class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                Save Assessment Settings
            </button>
        </form>
    </div>

    {{-- System info (read-only) --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="font-bold text-black/80 mb-5">System Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            @foreach([
                ['Laravel Version',  app()->version()],
                ['PHP Version',      PHP_VERSION],
                ['Environment',      app()->environment()],
                ['Timezone',         config('app.timezone')],
                ['Database',         config('database.default')],
                ['Cache Driver',     config('cache.default')],
                ['Queue Driver',     config('queue.default')],
                ['App URL',          config('app.url')],
            ] as [$label, $val])
            <div class="flex items-center justify-between p-3 bg-white rounded-lg">
                <span class="font-medium text-black/60">{{ $label }}</span>
                <code class="text-xs bg-white border border-black/15 px-2 py-1 rounded text-black/70 font-mono">{{ $val }}</code>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white rounded-2xl border bg-blue-200 p-8">
        <h2 class="font-bold text-blue-700 mb-2">Danger Zone</h2>
        <p class="text-sm text-black/50 mb-5">These actions are irreversible. Proceed with extreme caution.</p>
        <div class="space-y-3">
            <div class="flex items-center justify-between p-4 border bg-blue-100 rounded-xl">
                <div>
                    <p class="text-sm font-semibold text-black/80">Clear All Recommendations</p>
                    <p class="text-xs text-black/50">Removes all generated recommendations. Students will need to retake assessments.</p>
                </div>
                <button onclick="confirm('Clear ALL recommendations? This cannot be undone.') && alert('Feature available in production.')"
                        class="px-4 py-2 border bg-blue-400 bg-blue-600 rounded-lg text-sm font-medium hover:text-blue-50 transition-colors shrink-0 ml-4">
                    Clear
                </button>
            </div>
            <div class="flex items-center justify-between p-4 border bg-blue-100 rounded-xl">
                <div>
                    <p class="text-sm font-semibold text-black/80">Clear Activity Logs</p>
                    <p class="text-xs text-black/50">Removes all system activity logs older than 30 days.</p>
                </div>
                <button onclick="confirm('Clear old logs?') && alert('Feature available in production.')"
                        class="px-4 py-2 border bg-blue-400 bg-blue-600 rounded-lg text-sm font-medium hover:text-blue-50 transition-colors shrink-0 ml-4">
                    Clear Logs
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
