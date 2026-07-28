@extends('layouts.dashboard')

@section('title', __('parent.messages'))

@section('sidebar')
    @include('dashboard.parent.partials.sidebar')
@endsection

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-6 md:p-8 text-white shadow-lg relative overflow-hidden">
        <div class="absolute inset-0 opacity-10" style="background-image:radial-gradient(circle at 80% 20%,#fff 0%,transparent 60%)"></div>
        <div class="relative flex items-center gap-4">
            <div class="w-12 h-12 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-0.5">Guardian Portal</p>
                <h1 class="text-xl md:text-2xl font-bold">{{ __('parent.communicate') }}</h1>
                <p class="text-blue-200 text-sm mt-0.5">{{ __('parent.communicate_desc') }}</p>
            </div>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-xl px-4 py-3 text-sm font-semibold flex items-center gap-2">
        <svg class="w-4 h-4 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

        {{-- ══ LEFT: Compose + Inbox ══ --}}
        <div class="xl:col-span-1 space-y-5">

            {{-- Compose Message --}}
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 bg-blue-50/60 border-b border-blue-100 flex items-center gap-2">
                    <div class="w-7 h-7 bg-blue-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-3.5 h-3.5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    </div>
                    <h2 class="font-bold text-blue-900 text-sm">{{ __('parent.send_message') }}</h2>
                </div>
                <div class="p-5">
                    @if($teachers->isEmpty() && $counsellors->isEmpty())
                    <div class="bg-amber-50 border border-amber-200 text-amber-800 text-sm rounded-xl p-3 mb-4 flex items-start gap-2">
                        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        {{ __('parent.no_recipients') }}
                    </div>
                    @endif
                    <form action="{{ route('parent.messages.send') }}" method="POST" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-blue-700 mb-1.5">{{ __('parent.select_recipient') }}</label>
                            <select name="recipient_id" required
                                    class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400">
                                <option value="">{{ __('parent.select_recipient') }}</option>
                                @if($teachers->isNotEmpty())
                                <optgroup label="{{ __('parent.teachers') }}">
                                    @foreach($teachers as $t)
                                    <option value="{{ $t->id }}" @selected(old('recipient_id', $partner?->id ?? '') == $t->id || (($composeHint ?? '') === 'teacher' && $loop->first))>{{ $t->name }}</option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @if($counsellors->isNotEmpty())
                                <optgroup label="{{ __('parent.counsellors') }}">
                                    @foreach($counsellors as $c)
                                    <option value="{{ $c->id }}" @selected(old('recipient_id', $partner?->id ?? '') == $c->id || (($composeHint ?? '') === 'counsellor' && $loop->first))>{{ $c->name }}</option>
                                    @endforeach
                                </optgroup>
                                @endif
                                @if($children->isNotEmpty())
                                <optgroup label="{{ __('parent.children') }}">
                                    @foreach($children as $ch)
                                    <option value="{{ $ch->id }}" @selected(old('recipient_id') == $ch->id)>{{ $ch->name }}</option>
                                    @endforeach
                                </optgroup>
                                @endif
                            </select>
                            @error('recipient_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-700 mb-1.5">{{ __('parent.select_child') }}</label>
                            <select name="student_id"
                                    class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <option value="">—</option>
                                @foreach($children as $ch)
                                <option value="{{ $ch->id }}" @selected(old('student_id') == $ch->id)>{{ $ch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-700 mb-1.5">{{ __('parent.subject') }}</label>
                            <input type="text" name="subject" value="{{ old('subject') }}"
                                   placeholder="{{ __('parent.message_about_child') }}"
                                   class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-700 mb-1.5">{{ __('parent.your_message') }}</label>
                            <textarea name="body" rows="4" required
                                      class="w-full border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('body') }}</textarea>
                            @error('body')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit"
                                class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm transition-colors shadow-sm flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            {{ __('parent.send_message') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Inbox sidebar --}}
            <div class="bg-white rounded-2xl border border-blue-100 shadow-sm overflow-hidden">
                <div class="px-5 py-3.5 bg-blue-50/60 border-b border-blue-100">
                    <h3 class="font-bold text-blue-900 text-sm">{{ __('parent.inbox') }}</h3>
                </div>
                <div class="divide-y divide-blue-50">
                    @forelse($inbox as $msg)
                    <a href="{{ route('parent.messages', ['with' => $msg->sender_id]) }}"
                       class="flex items-start gap-3 px-4 py-3 hover:bg-blue-50/50 transition-colors {{ !$msg->read_at ? 'bg-blue-50/30' : '' }}">
                        <div class="w-8 h-8 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700 font-bold text-[10px] shrink-0">
                            {{ strtoupper(substr($msg->sender->name ?? 'U', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <p class="text-sm font-{{ !$msg->read_at ? 'bold' : 'semibold' }} text-black/80 truncate">{{ $msg->sender->name ?? '' }}</p>
                                @if(!$msg->read_at)
                                <span class="w-2 h-2 bg-blue-600 rounded-full shrink-0"></span>
                                @endif
                            </div>
                            <p class="text-xs text-black/50 truncate mt-0.5">{{ Str::limit($msg->body, 45) }}</p>
                        </div>
                    </a>
                    @empty
                    <div class="px-5 py-6 text-center">
                        <p class="text-sm text-black/40">{{ __('parent.no_messages') }}</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ══ RIGHT: Conversation ══ --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-blue-100 shadow-sm flex flex-col min-h-[480px] overflow-hidden">
            @if($partner)

            {{-- Conversation header --}}
            <div class="px-6 py-4 border-b border-blue-100 bg-blue-50/60 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-blue-200 border border-blue-300 flex items-center justify-center text-blue-800 font-bold text-sm shrink-0">
                    {{ strtoupper(substr($partner->name, 0, 2)) }}
                </div>
                <div>
                    <h2 class="font-bold text-blue-900">{{ $partner->name }}</h2>
                    <p class="text-xs text-blue-600 capitalize">{{ $partner->role }}</p>
                </div>
            </div>

            {{-- Messages --}}
            <div class="flex-1 p-5 space-y-4 overflow-y-auto" style="max-height:420px" id="msgContainer">
                @forelse($conversation as $msg)
                <div class="flex {{ $msg->sender_id === $parent->id ? 'justify-end' : 'justify-start' }} gap-2">
                    @if($msg->sender_id !== $parent->id)
                    <div class="w-7 h-7 rounded-full bg-blue-100 border border-blue-200 flex items-center justify-center text-blue-700 font-bold text-[10px] shrink-0 mt-1">
                        {{ strtoupper(substr($partner->name, 0, 2)) }}
                    </div>
                    @endif
                    <div class="max-w-[78%]">
                        <div class="rounded-2xl px-4 py-3 {{ $msg->sender_id === $parent->id
                            ? 'bg-blue-600 text-white rounded-tr-sm'
                            : 'bg-blue-50 text-black/80 border border-blue-100 rounded-tl-sm' }}">
                            @if($msg->student)
                            <p class="text-[10px] opacity-60 mb-0.5">Re: {{ $msg->student->name }}</p>
                            @endif
                            @if($msg->subject)
                            <p class="text-[10px] font-bold opacity-75 mb-1">{{ $msg->subject }}</p>
                            @endif
                            <p class="text-sm whitespace-pre-wrap leading-relaxed">{{ $msg->body }}</p>
                        </div>
                        <p class="text-[10px] text-black/35 mt-1 {{ $msg->sender_id === $parent->id ? 'text-right' : '' }}">
                            {{ $msg->created_at->format('d M Y, H:i') }}
                        </p>
                    </div>
                    @if($msg->sender_id === $parent->id)
                    <div class="w-7 h-7 rounded-full bg-blue-600 border border-blue-500 flex items-center justify-center text-white font-bold text-[10px] shrink-0 mt-1">
                        {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                    </div>
                    @endif
                </div>
                @empty
                <div class="flex items-center justify-center h-32">
                    <p class="text-center text-blue-400 text-sm">{{ __('parent.start_conversation') }}</p>
                </div>
                @endforelse
            </div>

            {{-- Reply box --}}
            <div class="p-4 border-t border-blue-100 bg-blue-50/30">
                <form action="{{ route('parent.messages.send') }}" method="POST" class="flex items-end gap-3">
                    @csrf
                    <input type="hidden" name="recipient_id" value="{{ $partner->id }}">
                    <textarea name="body" rows="2" required placeholder="{{ __('parent.your_message') }}…"
                              class="flex-1 border border-blue-200 bg-white rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400 resize-none">{{ old('body') }}</textarea>
                    <button type="submit"
                            class="h-10 w-10 bg-blue-600 hover:bg-blue-700 text-white rounded-xl flex items-center justify-center transition-colors shadow-sm shrink-0">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>

            @else
            <div class="flex-1 flex items-center justify-center p-12 text-center">
                <div>
                    <div class="w-16 h-16 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <p class="text-blue-800 font-semibold">{{ __('parent.no_messages') }}</p>
                    <p class="text-blue-500 text-sm mt-1">{{ __('parent.select_recipient_hint') }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    const mc = document.getElementById('msgContainer');
    if (mc) mc.scrollTop = mc.scrollHeight;
</script>
@endpush
@endsection
