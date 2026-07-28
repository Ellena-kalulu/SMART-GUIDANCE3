@extends('layouts.dashboard')
@section('title', 'Edit User')

@section('sidebar')
    @include('dashboard.admin.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('admin.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <a href="{{ route('admin.users') }}" class="text-black/50 hover:text-blue-600">Users</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Edit   <strong class="text-blue-600">{{ $user->name }}</strong></span>
</nav>
@endsection

@section('content')
<div class="space-y-6">

    {{-- User header --}}
    <div class="flex items-center gap-4">
        <div class="w-14 h-14 rounded-2xl bg-blue-600 flex items-center justify-center text-white font-black text-xl">
            {{ strtoupper(substr($user->name, 0, 2)) }}
        </div>
        <div>
            <h1 class="text-2xl font-black text-black">{{ $user->name }}</h1>
            <p class="text-black/50 text-sm">{{ $user->email }} Â· Joined {{ $user->created_at->format('d M Y') }}</p>
        </div>
    </div>

    {{-- Edit form --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="text-base font-bold text-black/80 mb-6">Account Details</h2>

        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('name')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    @error('email')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Role</label>
                    <select name="role"
                            class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        @foreach(['student','teacher','counsellor','parent','admin'] as $r)
                        <option value="{{ $r }}" {{ old('role', $user->role) === $r ? 'selected' : '' }}>
                            {{ ucfirst($r) }}
                        </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="role" value="{{ $user->role }}">
                        <p class="text-xs text-blue-600 mt-1">You cannot change your own role.</p>
                    @endif
                    @error('role')<p class="text-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Account Status</label>
                    <select name="is_active"
                            class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100"
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                        <option value="1" {{ old('is_active', $user->is_active) ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ !old('is_active', $user->is_active) ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @if($user->id === auth()->id())
                        <input type="hidden" name="is_active" value="1">
                    @endif
                </div>
            </div>

            <div class="flex gap-3 pt-2 border-t border-black/10">
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                    Save Changes
                </button>
                <a href="{{ route('admin.users') }}"
                   class="px-6 py-3 border border-black/15 text-black/60 rounded-xl text-sm font-medium hover:bg-white transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Change Password --}}
    <div class="bg-white rounded-2xl border border-black/15 p-8">
        <h2 class="text-base font-bold text-black/80 mb-6">Reset Password</h2>
        <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-5">
            @csrf @method('PUT')
            {{-- Pass existing values so validation doesn't fail on the main fields --}}
            <input type="hidden" name="name" value="{{ $user->name }}">
            <input type="hidden" name="email" value="{{ $user->email }}">
            <input type="hidden" name="role" value="{{ $user->role }}">
            <input type="hidden" name="is_active" value="{{ $user->is_active ? 1 : 0 }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">New Password</label>
                    <input type="password" name="password" placeholder="Leave blank to keep current"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    @error('password')<p class="ring-blue-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-black/70 mb-1.5">Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Repeat new password"
                           class="w-full px-4 py-3 border border-black/15 rounded-xl text-sm focus:outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                </div>
            </div>
            <button type="submit"
                    class="px-6 py-3 bg-black/80 hover:bg-black/70 text-white font-semibold rounded-xl text-sm transition-colors">
                Update Password
            </button>
        </form>
    </div>

    {{-- Danger zone --}}
    @if($user->id !== auth()->id())
    <div class="bg-white rounded-2xl border bg-blue-200 p-8">
        <h2 class="text-base font-bold text-blue-700 mb-2">Danger Zone</h2>
        <p class="text-sm text-black/50 mb-4">Permanently delete this user and all associated data.</p>
        <form action="{{ route('admin.users.delete', $user) }}" method="POST"
              onsubmit="return confirm('Delete {{ $user->name }} permanently? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition-colors">
                Delete User
            </button>
        </form>
    </div>
    @endif
</div>
@endsection
