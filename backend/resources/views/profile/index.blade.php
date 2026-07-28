@extends('layouts.dashboard')

@section('title', 'Profile Settings')

@section('sidebar')
<div class="space-y-2">
@switch(auth()->user()->role)
    @case('admin')
         @include('dashboard.admin.partials.sidebar')
        @break

    @case('student')
        @include('dashboard.student.partials.sidebar')
        @break

    @case('teacher')
        @include('dashboard.teacher.partials.sidebar')
        @break

    @case('counsellor')
        @include('dashboard.counsellor.partials.sidebar')
        @break

    @case('parent')
        @include('dashboard.parent.partials.sidebar')
        @break

    @default
        <x-sidebar-nav :route="'admin.dashboard'" :active="false" label="Dashboard">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </x-sidebar-nav>
@endswitch
</div>
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Profile Settings</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- Profile card --}}
    <div class="bg-white rounded-2xl border border-black/15 overflow-hidden">
        <div class="bg-gradient-to-r from-[#1e40af] to-[#2563eb] px-8 py-8 flex items-center gap-5">
            <div class="w-16 h-16 rounded-2xl bg-white/20 border-2 border-white/40 flex items-center justify-center text-white font-black text-2xl">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
            <div>
                <h1 class="text-white font-bold text-2xl">{{ $user->name }}</h1>
                <p class="text-blue-200 text-sm capitalize mt-0.5">{{ $user->role }} · Luwinga Secondary School</p>
                @if($user->isStudent() && $profile)
                    <p class="text-blue-300 text-xs mt-1">{{ $profile->student_number }} · {{ $profile->form_level }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Update profile form --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="text-lg font-bold text-black/80 mb-6">Personal Information</h2>

        <form action="{{ route('profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                           required>
                    @error('name')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                           required>
                    @error('email')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            @if($user->isStudent() && $profile)
            <div class="pt-2 border-t border-black/10">
                <h3 class="text-sm font-semibold text-black/60 mb-4 uppercase tracking-wide">Student Details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Form Level</label>
                        <select name="form_level"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach(['Form 1','Form 2','Form 3','Form 4'] as $form)
                            <option value="{{ $form }}" {{ $profile->form_level === $form ? 'selected' : '' }}>
                                {{ $form }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Stream</label>
                        <select name="stream"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach(['Sciences','Humanities','Languages','Commerce','General'] as $stream)
                            <option value="{{ $stream }}" {{ $profile->stream === $stream ? 'selected' : '' }}>
                                {{ $stream }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Gender</label>
                        <select name="gender"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select</option>
                            @foreach(['male','female','other'] as $g)
                            <option value="{{ $g }}" {{ $profile->gender === $g ? 'selected' : '' }}>
                                {{ ucfirst($g) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Disability Type</label>
                        <select name="disability_type"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            @foreach([
                                'none' => 'None',
                                'visual_impairment' => 'Visual Impairment',
                                'hearing_impairment' => 'Hearing Impairment',
                                'physical_disability' => 'Physical Disability',
                                'learning_disability' => 'Learning Disability',
                            ] as $val => $label)
                            <option value="{{ $val }}" @selected(($profile->disability_type ?? 'none') === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-black/40 mt-1">Accessibility adapts automatically to your selection.</p>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Interests</label>
                    <textarea name="interests" rows="2"
                              class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                              placeholder="e.g. Technology, Medicine, Agriculture...">{{ $profile->interests }}</textarea>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Skills</label>
                    <textarea name="skills" rows="2"
                              class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 resize-none"
                              placeholder="e.g. Problem solving, communication, mathematics...">{{ $profile->skills }}</textarea>
                </div>
            </div>
            @endif

            <div class="pt-2">
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Save Changes
                </button>
            </div>
        </form>
    </div>

    {{-- Change password --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="text-lg font-bold text-black/80 mb-6">Change Password</h2>

        <form action="{{ route('profile.password') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-black/70 mb-1.5">Current Password</label>
                <input type="password" name="current_password"
                       class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all max-w-sm"
                       placeholder="Enter current password">
                @error('current_password')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 max-w-lg">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">New Password</label>
                    <input type="password" name="password"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                           placeholder="Min. 8 characters">
                    @error('password')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all"
                           placeholder="Repeat new password">
                </div>
            </div>

            <button type="submit"
                    class="px-6 py-3 bg-black/80 hover:bg-black/70 text-white font-semibold rounded-xl text-sm transition-colors">
                Update Password
            </button>
        </form>
    </div>

    {{-- Danger zone --}}
    <div class="bg-white rounded-2xl border bg-blue-200 p-8" x-data="{ open: false, password: '' }">
        <h2 class="text-lg font-bold text-blue-700 mb-2">Danger Zone</h2>
        <p class="text-sm text-black/50 mb-4">
            Once you delete your account, all your data including assessments, recommendations, and progress will be permanently removed. This cannot be undone.
        </p>
        <button type="button" @click="open = true"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
            Delete My Account
        </button>

        <!-- Confirmation modal -->
        <div x-show="open" x-cloak
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md p-6" @click.outside="open = false">
                <h3 class="text-lg font-bold text-black/80 mb-2">Confirm Account Deletion</h3>
                <p class="text-sm text-black/50 mb-4">
                    Enter your current password to permanently delete your account.
                </p>
                <form action="{{ route('profile.delete') }}" method="POST">
                    @csrf @method('DELETE')
                    <input type="password" name="password" x-model="password"
                           placeholder="Your current password"
                           class="w-full px-4 py-2.5 border border-black/15 rounded-xl text-sm mb-4
                                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="flex gap-3">
                        <button type="button" @click="open = false"
                                class="flex-1 px-4 py-2.5 border border-black/15 rounded-xl text-sm font-semibold text-black/60 hover:bg-white transition-colors">
                            Cancel
                        </button>
                        <button type="submit" :disabled="password.length < 1"
                                class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed text-white rounded-xl text-sm font-semibold transition-colors">
                            Delete Permanently
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
