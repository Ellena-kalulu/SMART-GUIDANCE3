@extends('layouts.auth')

@section('title', 'Set New Password')

@section('auth-content')

<div class="mb-8">
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-black/50 hover:text-blue-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Sign In
    </a>

    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
        <svg class="w-6 h-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
    </div>

    <h1 class="text-black font-black text-3xl tracking-tight mb-2">Set New Password</h1>
    <p class="text-black/50">Choose a strong password for your account</p>
</div>

@if($errors->any())
<div class="mb-6 p-4 bg-blue-50 border bg-blue-200 rounded-xl">
    @foreach($errors->all() as $error)
        <p class="bg-blue-600 text-sm">{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('password.update') }}" class="space-y-5">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <div>
        <label for="email" class="block text-sm font-semibold text-black/70 mb-2">Email Address</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <input type="email" name="email" id="email" value="{{ old('email', $request->email) }}" required readonly
                   class="w-full pl-10 pr-4 py-3.5 border border-black/15 rounded-xl bg-white text-black/50 cursor-not-allowed">
        </div>
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-black/70 mb-2">New Password</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <input type="password" name="password" id="password" required
                   class="w-full pl-10 pr-12 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Enter new password">
            <button type="button" onclick="togglePassword('password')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 hover:text-black/60">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
        <div class="mt-3">
            <div class="flex gap-1 mb-2">
                <div class="h-1 flex-1 rounded-full bg-black/[0.05]" id="bar1"></div>
                <div class="h-1 flex-1 rounded-full bg-black/[0.05]" id="bar2"></div>
                <div class="h-1 flex-1 rounded-full bg-black/[0.05]" id="bar3"></div>
                <div class="h-1 flex-1 rounded-full bg-black/[0.05]" id="bar4"></div>
            </div>
        </div>
    </div>

    <div>
        <label for="password_confirmation" class="block text-sm font-semibold text-black/70 mb-2">Confirm Password</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <input type="password" name="password_confirmation" id="password_confirmation" required
                   class="w-full pl-10 pr-12 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Confirm new password">
            <button type="button" onclick="togglePassword('password_confirmation')"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 hover:text-black/60">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    <button type="submit" class="btn-primary">
        Update Password
    </button>
</form>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    field.type = field.type === 'password' ? 'text' : 'password';
}

const password = document.getElementById('password');
password.addEventListener('input', function() {
    const val = this.value;
    const bars = [1,2,3,4].map(n => document.getElementById('bar'+n));
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['text-blue-600', 'text-blue-600', 'text-blue-600', 'text-blue-600'];
    bars.forEach((bar, i) => {
        bar.className = `h-1 flex-1 rounded-full transition-all ${i < score ? colors[score-1] : 'bg-black/[0.05]'}`;
    });
});
</script>

@endsection
