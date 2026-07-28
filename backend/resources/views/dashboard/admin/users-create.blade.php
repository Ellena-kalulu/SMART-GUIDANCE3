@extends('layouts.dashboard')
@section('title', 'Create User')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Admin</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.users') }}" class="text-black/50 hover:text-blue-600">User Management</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Create User</span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    <div>
        <h1 class="text-2xl font-black text-black">Create New User</h1>
        <p class="text-black/50 text-sm mt-0.5">Add a student, teacher, counsellor, parent or admin account.</p>
    </div>

    <div class="bg-white rounded-2xl border border-black/15 p-8" x-data="{ role: '{{ old('role','student') }}' }">
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-5">
            @csrf

            {{-- Role selector --}}
            <div>
                <label class="block text-sm font-semibold text-black/70 mb-2">Role</label>
                <div class="grid grid-cols-3 md:grid-cols-5 gap-2">
                    @foreach([
                        ['student',    'Student'],
                        ['teacher',    'Teacher'],
                        ['counsellor', 'Counsellor'],
                        ['parent',     'Parent'],
                        ['admin',      'Admin'],
                    ] as [$val, $label])
                    <label class="cursor-pointer">
                        <input type="radio" name="role" value="{{ $val }}"
                               x-model="role"
                               {{ old('role','student') === $val ? 'checked' : '' }}
                               class="sr-only">
                        <div class="flex flex-col items-center gap-1 px-3 py-3 rounded-xl border-2 text-center transition-all text-sm font-medium"
                             :class="role === '{{ $val }}'
                                ? 'border-brand-600 bg-brand-50 text-brand-700'
                                : 'border-black/15 text-black/60 hover:border-blue-300'">
                            <span class="text-xs font-bold w-8 h-8 rounded-full bg-brand-100 text-brand-700 flex items-center justify-center">{{ strtoupper(substr($label, 0, 1)) }}</span>
                            <span class="text-xs">{{ $label }}</span>
                        </div>
                    </label>
                    @endforeach
                </div>
                @error('role')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
            </div>

            {{-- Name & Email --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           placeholder="e.g. Chisomo Phiri"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('name')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                           placeholder="user@example.com"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('email')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Student-specific fields --}}
            <div x-show="role === 'student'" class="space-y-4 border-t border-black/10 pt-5">
                <p class="text-xs font-semibold text-black/50 uppercase tracking-wide">Student Details</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Form Level</label>
                        <select name="form_level"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select form</option>
                            @foreach(['Form 1','Form 2','Form 3','Form 4'] as $f)
                            <option value="{{ $f }}" {{ old('form_level') === $f ? 'selected' : '' }}>{{ $f }}</option>
                            @endforeach
                        </select>
                        @error('form_level')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-black/70 mb-1.5">Stream</label>
                        <select name="stream"
                                class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                            <option value="">Select stream</option>
                            @foreach(['Sciences','Humanities','Languages','Commerce','General'] as $s)
                            <option value="{{ $s }}" {{ old('stream') === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Password --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 border-t border-black/10 pt-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Password</label>
                    <div class="relative">
                        <input type="password" name="password" id="pw" required
                               placeholder="Min. 8 characters"
                               class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 pr-10">
                        <button type="button" onclick="const i=document.getElementById('pw');i.type=i.type==='password'?'text':'password'"
                                class="absolute right-3 top-1/2 -translate-y-1/2 text-black/40 hover:text-black/60">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                           placeholder="Repeat password"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>

            {{-- Active toggle --}}
            <div class="flex items-center gap-3 pt-2">
                <input type="checkbox" name="is_active" id="is_active" value="1" checked
                       class="w-4 h-4 rounded border-black/20 text-blue-600 focus:ring-blue-500 cursor-pointer">
                <label for="is_active" class="text-sm text-black/70 cursor-pointer">
                    Account is active immediately
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2 border-t border-black/10">
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Create User
                </button>
                <a href="{{ route('admin.users') }}"
                   class="px-6 py-3 border border-black/15 text-black/60 rounded-xl text-sm font-medium hover:bg-white transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
