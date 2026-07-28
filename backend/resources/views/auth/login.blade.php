@extends('layouts.auth')

@section('title', 'Sign In')

@section('auth-content')

<div class="text-center mb-6">
    <h1 class="text-black font-black text-3xl tracking-tight mb-2">Welcome Back</h1>
    <p class="text-black/50">Sign in to Luwinga Secondary School Career Guidance</p>
</div>

@if(session('status'))
<div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-800 text-sm flex items-start gap-3">
    <svg class="w-5 h-5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <span>{{ session('status') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
    @foreach($errors->all() as $error)
        <p class="text-sm flex text-red-700 items-center gap-2">
            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ $error }}
        </p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="login" class="block text-sm font-semibold text-black/70 mb-2">Email / Username</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus autocomplete="username"
                   class="w-full pl-10 pr-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Email or student number">
        </div>
        <p class="text-xs text-black/40 mt-1">Students can use their student number (e.g. LSS/2024/0001)</p>
    </div>

    <div>
        <div class="flex items-center justify-between mb-2">
            <label for="password" class="block text-sm font-semibold text-black/70">Password</label>
            <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-700 font-medium">Forgot password?</a>
        </div>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <input type="password" name="password" id="password" required
                   class="w-full pl-10 pr-12 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="Enter your password">
            <button type="button" onclick="togglePassword()"
                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-black/40 hover:text-black/60">
                <svg id="eyeIcon" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 border-black/20 rounded focus:ring-blue-500">
            <span class="text-sm text-black/60">Remember me</span>
        </label>
    </div>

    <button type="submit" class="w-full py-3.5 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 shadow-lg shadow-blue-600/20 transition-all">
        Sign In
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-black/60">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:text-blue-700 ml-1">Create Account</a>
    </p>
</div>

<script>
function togglePassword() {
    const password = document.getElementById('password');
    password.type = password.type === 'password' ? 'text' : 'password';
}
</script>

@endsection
