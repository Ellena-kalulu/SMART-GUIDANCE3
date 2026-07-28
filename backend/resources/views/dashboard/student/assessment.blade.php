@extends('layouts.dashboard')

@section('title', 'Self Assessment')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Self Assessment</span>
</nav>
@endsection

@php
    $sectionStarts = []; $sectionList = []; $prevSection = null;
    foreach ($questions as $i => $q) {
        if ($q->section !== $prevSection) {
            $sectionStarts[$i] = $q->section;
            $sectionList[] = $q->section;
            $prevSection = $q->section;
        }
    }
    $totalSections = count($sectionList);
    $totalQuestions = $questions->count();
    $requiredMeta = $questions->values()->map(fn ($q, $idx) => [
        'idx' => $idx, 'type' => $q->type,
        'optional' => in_array($q->type, ['text', 'checkbox'], true),
        'qid' => $q->id,
    ])->values();
@endphp

@section('content')
<div class="max-w-3xl mx-auto pb-8" x-data="selfAssessment({{ $totalQuestions }})">

    @if(session('error'))
    <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm">{{ session('error') }}</div>
    @endif

    {{-- Detailed survey explanation --}}
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-blue-700 to-blue-900 p-6 md:p-8 text-white">
        <p class="text-blue-200 text-xs font-bold uppercase tracking-widest mb-2">Career Guidance Survey</p>
        <h1 class="text-2xl md:text-3xl font-bold mb-3">Self Assessment</h1>
        <div class="space-y-3 text-sm text-blue-50 leading-relaxed max-w-2xl">
            <p>This survey helps the system understand <strong>who you are</strong>, <strong>what you enjoy</strong>, and <strong>what you want to do</strong> in the future.</p>
            <p>Your answers are combined with <strong>grades uploaded by your teacher</strong> to recommend careers, subject combinations, and university programmes. Grades matter a lot — be honest, but know that your school results strongly affect recommendations.</p>
            <ul class="list-disc pl-5 space-y-1 text-blue-100">
                <li>There are <strong>{{ $totalSections }} sections</strong> and <strong>{{ $totalQuestions }} questions</strong>.</li>
                <li>Tap an answer to select it, then press <strong>Next</strong>.</li>
                <li>Some questions let you pick <strong>more than one</strong> answer.</li>
                <li>Open questions are optional — you can skip them.</li>
            </ul>
            <p class="text-blue-200 text-xs">There is no pass or fail. Answer honestly so we can guide you well.</p>
        </div>
    </div>

    <div class="mb-4 flex gap-2 flex-wrap">
        @foreach($sectionList as $sIdx => $sName)
        @php
            $sFirst = array_search($sName, $sectionStarts);
            $sectionKeys = array_keys($sectionStarts);
            $sNextKey = $sectionKeys[$sIdx + 1] ?? $totalQuestions;
        @endphp
        <div class="px-3 py-1.5 rounded-full text-xs font-bold border"
             :class="current >= {{ $sFirst }} && current < {{ $sNextKey }} ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-black/40 border-blue-100'">
            Section {{ chr(65 + $sIdx) }}
        </div>
        @endforeach
    </div>

    <div class="mb-6">
        <div class="flex justify-between text-xs text-black/50 mb-1">
            <span>Question <span x-text="current + 1"></span> / {{ $totalQuestions }}</span>
        </div>
        <div class="h-2 bg-blue-100 rounded-full"><div class="h-full bg-blue-600 rounded-full transition-all" :style="'width:'+((current+1)/{{ $totalQuestions }}*100)+'%'"></div></div>
    </div>

    <form action="{{ route('student.assessment.submit') }}" method="POST" id="assessmentForm">
        @csrf

        @foreach($questions as $i => $question)
        @php $scaleLabels = $question->scale_labels ? json_decode($question->scale_labels, true) : null; @endphp
        <div x-show="current === {{ $i }}" class="bg-white rounded-2xl border-2 border-blue-100 p-5 sm:p-7 shadow-sm mb-4">

            @if(isset($sectionStarts[$i]))
            <p class="text-xs font-bold text-blue-600 uppercase mb-3">{{ $sectionStarts[$i] }}</p>
            @endif

            <h2 class="text-lg font-bold text-black/85 mb-5">{{ $question->question_text }}</h2>

            @if($question->type === 'multiple_choice')
            <input type="hidden" name="answers[{{ $question->id }}]" :value="answers[{{ $i }}] || ''">
            <div class="space-y-2">
                @foreach($question->options as $option)
                <button type="button" @click="pickMc({{ $i }}, {{ $option->id }})"
                        class="w-full text-left flex items-center gap-3 p-4 rounded-xl border-2 transition-all cursor-pointer"
                        :class="answers[{{ $i }}] == {{ $option->id }} ? 'border-blue-600 bg-blue-50' : 'border-blue-100 hover:border-blue-300'">
                    <span class="w-5 h-5 rounded-full border-2 shrink-0 flex items-center justify-center"
                          :class="answers[{{ $i }}] == {{ $option->id }} ? 'border-blue-600 bg-blue-600' : 'border-blue-300'">
                        <span class="w-2 h-2 rounded-full bg-white" x-show="answers[{{ $i }}] == {{ $option->id }}"></span>
                    </span>
                    <span class="text-sm font-medium text-black/75">{{ $option->option_text }}</span>
                </button>
                @endforeach
            </div>
            @endif

            @if($question->type === 'checkbox')
            <p class="text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mb-3">Pick all subjects you enjoy. You can choose more than one.</p>
            <input type="hidden" name="text_answers[{{ $question->id }}]" :value="(checkboxAnswers[{{ $i }}] || []).join(' | ')">
            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                @foreach($question->options as $option)
                <button type="button" @click="toggleCheckbox({{ $i }}, @js($option->option_text))"
                        class="w-full text-left flex items-center gap-3 p-3 rounded-xl border-2 transition-all cursor-pointer"
                        :class="(checkboxAnswers[{{ $i }}] || []).includes(@js($option->option_text)) ? 'border-blue-600 bg-blue-50' : 'border-blue-100 hover:border-blue-300'">
                    <span class="w-5 h-5 rounded border-2 shrink-0 flex items-center justify-center text-xs"
                          :class="(checkboxAnswers[{{ $i }}] || []).includes(@js($option->option_text)) ? 'border-blue-600 bg-blue-600 text-white' : 'border-blue-300'">✓</span>
                    <span class="text-sm text-black/75">{{ $option->option_text }}</span>
                </button>
                @endforeach
            </div>
            @endif

            @if($question->type === 'scale')
            <p class="text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mb-3">Tap a number from 1 (low) to 5 (high).</p>
            <input type="hidden" name="answers[{{ $question->id }}]" :value="answers[{{ $i }}] || ''">
            <div class="flex justify-between text-xs text-black/45 mb-2">
                <span>{{ $scaleLabels[1] ?? 'Low' }}</span><span>{{ $scaleLabels[5] ?? 'High' }}</span>
            </div>
            <div class="flex gap-2">
                @foreach([1,2,3,4,5] as $val)
                <button type="button" @click="pickScale({{ $i }}, {{ $val }})"
                        class="flex-1 py-4 rounded-xl border-2 text-lg font-bold cursor-pointer transition-all"
                        :class="answers[{{ $i }}] == {{ $val }} ? 'border-blue-600 bg-blue-600 text-white' : 'border-blue-100 text-black/50 hover:bg-blue-50'">{{ $val }}</button>
                @endforeach
            </div>
            @endif

            @if($question->type === 'text')
            <p class="text-sm text-blue-700 bg-blue-50 border border-blue-100 rounded-lg px-3 py-2 mb-3">Write a short answer. This is optional.</p>
            <textarea name="text_answers[{{ $question->id }}]" rows="3" x-model="textAnswers[{{ $i }}]"
                      @input="answers[{{ $i }}] = $event.target.value.trim() ? 1 : null"
                      class="w-full px-4 py-3 border-2 border-blue-100 rounded-xl text-sm focus:border-blue-500 resize-none"
                      placeholder="Type here… (optional)"></textarea>
            @endif
        </div>
        @endforeach

        <div class="flex justify-between gap-3 bg-white rounded-2xl border-2 border-blue-100 p-4 shadow-lg sticky bottom-4">
            <button type="button" @click="prev()" x-show="current > 0" class="px-5 py-2.5 border-2 border-blue-200 rounded-xl text-sm font-semibold text-blue-700 cursor-pointer">← Back</button>
            <span x-show="current === 0"></span>
            <button type="button" @click="next()" x-show="current < {{ $totalQuestions - 1 }}" class="px-6 py-2.5 bg-blue-600 text-white rounded-xl text-sm font-bold cursor-pointer">Next →</button>
            <button type="button" @click="submitForm()" x-show="current === {{ $totalQuestions - 1 }}" class="px-6 py-2.5 bg-blue-700 text-white rounded-xl text-sm font-bold cursor-pointer">Submit Assessment</button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function selfAssessment(total) {
    const requiredTypes = @json($requiredMeta);
    return {
        current: 0, answers: {}, checkboxAnswers: {}, textAnswers: {},
        pickMc(idx, optId) { this.answers[idx] = optId; },
        pickScale(idx, val) { this.answers[idx] = val; },
        toggleCheckbox(idx, value) {
            if (!this.checkboxAnswers[idx]) this.checkboxAnswers[idx] = [];
            const pos = this.checkboxAnswers[idx].indexOf(value);
            if (pos === -1) this.checkboxAnswers[idx].push(value);
            else this.checkboxAnswers[idx].splice(pos, 1);
            this.answers[idx] = this.checkboxAnswers[idx].length ? 1 : null;
        },
        isAnswered(idx) {
            const q = requiredTypes.find(r => r.idx === idx);
            return !q || q.optional || !!this.answers[idx];
        },
        next() {
            if (!this.isAnswered(this.current)) { alert('Please choose an answer first.'); return; }
            if (this.current < total - 1) { this.current++; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        prev() {
            if (this.current > 0) { this.current--; window.scrollTo({ top: 0, behavior: 'smooth' }); }
        },
        submitForm() {
            for (const q of requiredTypes) {
                if (!q.optional && !this.answers[q.idx]) {
                    this.current = q.idx;
                    alert('Please answer question ' + (q.idx + 1) + ' before submitting.');
                    return;
                }
            }
            document.getElementById('assessmentForm').submit();
        },
    };
}
</script>
@endpush
@endsection
