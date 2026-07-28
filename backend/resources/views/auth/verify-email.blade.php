@extends('layouts.auth')

@section('title', 'Verify Your Email')

@section('auth-content')

<div class="mb-8 text-center">
    <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-blue-100 mb-4">
        <svg class="w-7 h-7 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
        </svg>
    </div>
    <h1 class="text-black font-black text-3xl tracking-tight mb-2">Check your inbox</h1>
    <p class="text-black/50 text-sm">
        We sent a verification link to<br>
        <span class="font-semibold text-black/70">{{ auth()->user()->email }}</span>
    </p>
</div>

@if(session('status'))
<div class="mb-5 p-4 text-blue-50 border text-blue-200 rounded-xl bg-blue-700 text-sm flex items-start gap-3">
    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
    </svg>
    <span>{{ session('status') }}</span>
</div>
@endif

<div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 text-sm text-blue-700">
    <ul class="space-y-1.5">
        <li class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
            </svg>
            Open the email and click <strong>Verify Email Address</strong>
        </li>
        <li class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            Check your <strong>spam</strong> folder if you can't find it
        </li>
        <li class="flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Testing locally? View emails at
            <a href="http://localhost:8025" target="_blank" class="font-mono font-semibold underline underline-offset-2">localhost:8025</a>
        </li>
    </ul>
</div>

<div class="space-y-3">
    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn-primary">
            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Resend Verification Email
        </button>
    </form>

    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                class="w-full py-3.5 border border-black/15 hover:bg-white text-black/60 font-semibold text-sm rounded-xl transition-colors">
            Sign Out
        </button>
    </form>
</div>

@endsection
