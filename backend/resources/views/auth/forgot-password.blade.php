@extends('layouts.auth')

@section('title', 'Reset Password')

@section('auth-content')

<div class="mb-8">
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-sm text-black/50 hover:text-blue-600 transition-colors mb-6">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Sign In
    </a>

    <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mb-5">
        <svg class="w-8 h-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
        </svg>
    </div>

    <h1 class="text-black font-black text-3xl tracking-tight mb-2">Forgot Password?</h1>
    <p class="text-black/50">No worries. Enter your email and we'll send you reset instructions.</p>
</div>

@if(session('status'))
<div class="mb-6 p-4 bg-blue-50 border bg-blue-200 rounded-xl bg-blue-700 text-sm flex items-start gap-3">
    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <span>{{ session('status') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-6 p-4 bg-blue-50 border bg-blue-200 rounded-xl">
    @foreach($errors->all() as $error)
        <p class="bg-blue-600 text-sm">{{ $error }}</p>
    @endforeach
</div>
@endif

<form method="POST" action="{{ route('password.email') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-sm font-semibold text-black/70 mb-2">Email Address</label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                   class="w-full pl-10 pr-4 py-3.5 border border-black/15 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all"
                   placeholder="you@example.com">
        </div>
    </div>

    <button type="submit" class="btn-primary">
        Send Reset Link
    </button>
</form>

<div class="mt-6 text-center">
    <p class="text-sm text-black/60">
        Remember your password?
        <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:text-blue-700">Sign In</a>
    </p>
</div>

@endsection
