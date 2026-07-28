@extends('layouts.dashboard')

@section('title', 'Subject Combinations')

@section('sidebar')
    @include('dashboard.student.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('student.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
    <span class="text-black/80 font-medium">Subject Combinations</span>
</nav>
@endsection

@section('content')
@php
    $topPath    = $mscePaths[0] ?? null;
    $confLevel  = $analysis?->confidence_level ?? 'low';
    $confLabel  = ['very_high' => 'Very High', 'high' => 'High', 'moderate' => 'Moderate', 'low' => 'Low'][$confLevel] ?? 'Low';
    $hasgrades  = $topPath['has_grade_data'] ?? false;
    $hasAssmt   = $analysis !== null;
    $currentForm = $profile?->form_level;
    $isEarlyForm = in_array($currentForm, ['Form 1', 'Form 2']);

    $ratings = [];
    foreach ($mscePaths as $p) {
        $s = $p['score'];
        $stars = $s >= 88 ? 5 : ($s >= 73 ? 4 : ($s >= 58 ? 3 : ($s >= 43 ? 2 : 1)));
        $ratings[] = [
            'stars' => $stars,
            'empty' => 5 - $stars,
            'label' => $s >= 88 ? 'Highly Recommended' : ($s >= 73 ? 'Good Match' : ($s >= 58 ? 'Possible Match' : ($s >= 43 ? 'Less Suitable' : 'Not Recommended'))),
        ];
    }
    $topRating = $ratings[0] ?? null;

    $nrCount  = count($topPath['not_ready_reasons'] ?? []);
    $riskLevel = $nrCount === 0 ? 'Low' : ($nrCount === 1 ? 'Medium' : 'High');

    $readyCount = 0;
    $totalSubs  = count($topPath['subjects'] ?? []);
    foreach ($topPath['subjects'] ?? [] as $sub) {
        $gs = $topPath['grade_scores'][$sub] ?? null;
        if ($gs !== null && $gs >= 55) $readyCount++;
    }
    $readyPct = $totalSubs > 0 ? round(($readyCount / $totalSubs) * 100) : 0;
    $shortName = fn($n) => str_replace(' Path', '', $n ?? '');

    // Saved preferences — handle both old (array) and new (string) formats
    $savedSubjects      = $savedPrefs['preferred_subjects'] ?? [];
    $savedCareersTxt    = is_array($savedPrefs['career_interests'] ?? '') ? implode(', ', $savedPrefs['career_interests'] ?? []) : ($savedPrefs['career_interests'] ?? '');
    $savedInterestsTxt  = is_array($savedPrefs['personal_interests'] ?? '') ? implode(', ', $savedPrefs['personal_interests'] ?? []) : ($savedPrefs['personal_interests'] ?? '');
    $savedStrengths     = $savedPrefs['strengths'] ?? [];
    $savedLearning      = $savedPrefs['learning_preference'] ?? '';
    $savedAdditional    = $savedPrefs['additional_info'] ?? '';
    $savedGoals         = $profile?->preferred_career ?? '';
    $prefsComplete      = !empty($savedSubjects) || !empty($savedCareersTxt) || !empty($savedGoals);

    // Show recommendation if there is data, OR if the student just clicked Analyse
    $hasResults = $topPath && !$isEarlyForm && ($hasgrades || $hasAssmt || ($analyzed ?? false) || $prefsComplete);
@endphp

<div class="space-y-5">

{{-- ── Header Banner ─────────────────────────────────────────────────────── --}}
<div class="bg-gradient-to-r from-blue-700 to-blue-900 rounded-2xl p-6 md:p-8 text-white shadow-lg">
    <p class="text-blue-200 text-xs font-semibold uppercase tracking-widest mb-2">Personalised Guidance</p>
    <h1 class="text-2xl md:text-3xl font-bold">Subject Combination Guidance</h1>
    <p class="text-white/75 text-sm mt-2 max-w-2xl">Fill in your details below and click <strong class="text-white">Analyse Subject Combination</strong>. Your grades are uploaded by your teacher — only the preference form below needs to be filled in by you.</p>
    <div class="mt-4 flex flex-wrap items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-white/60 text-sm">Planning for:</span>
            <form method="GET" class="inline">
                <select name="target_form" onchange="this.form.submit()"
                        class="border border-white/30 bg-white/10 text-white rounded-lg px-3 py-1.5 text-sm font-semibold focus:outline-none">
                    <option value="Form 3" @selected($targetForm === 'Form 3') class="text-black bg-white">Form 3</option>
                    <option value="Form 4" @selected($targetForm === 'Form 4') class="text-black bg-white">Form 4</option>
                </select>
            </form>
        </div>
        @if($currentForm)
        <span class="text-white/50 text-sm">Current Form: <strong class="text-white">{{ $currentForm }}</strong></span>
        @endif
        @if($hasResults)
        <a href="#recommendation" class="ml-auto text-xs font-bold text-white/80 hover:text-white underline">
            View My Recommendation ↓
        </a>
        @endif
    </div>
</div>

{{-- ── Alerts ────────────────────────────────────────────────────────────── --}}
@include('partials.alerts')

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- PREFERENCE FORM                                                         --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<form method="POST" action="{{ route('student.subject.combinations.preferences') }}">
    @csrf
    <div class="space-y-5">

    {{-- 1. Student Information ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">1</div>
            <div>
                <h2 class="font-bold text-blue-900">Student Information</h2>
                <p class="text-xs text-blue-600 mt-0.5">Auto-filled from your profile — contact admin to update</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 md:grid-cols-3 gap-5">
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Full Name</p>
                <p class="font-semibold text-black/80">{{ $user->name }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Student Number</p>
                <p class="font-semibold text-black/80">{{ $profile?->student_number ?? '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Form Level</p>
                <p class="font-bold text-blue-700">{{ $profile?->form_level ?? 'Not set' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Stream / Track</p>
                <p class="font-semibold text-black/80 capitalize">{{ $profile?->stream ?? '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Gender</p>
                <p class="font-semibold text-black/80 capitalize">{{ $profile?->gender ?? '—' }}</p>
            </div>
            <div>
                <p class="text-[10px] font-bold text-black/35 uppercase mb-1">Email</p>
                <p class="font-semibold text-black/75 text-sm truncate">{{ $user->email }}</p>
            </div>
        </div>
    </div>

    {{-- 2. Academic Performance (read-only) ──────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">2</div>
            <div>
                <h2 class="font-bold text-blue-900">Academic Performance</h2>
                <p class="text-xs text-blue-600 mt-0.5">Teacher-uploaded grades — these form 70% of your recommendation (read-only)</p>
            </div>
        </div>
        @if($form12Subjects->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-[10px] uppercase text-black/40 bg-blue-50/50 border-b border-black/[0.06]">
                        <th class="px-5 py-3 font-bold">Subject</th>
                        <th class="px-5 py-3 font-bold">Score</th>
                        <th class="px-5 py-3 font-bold">How You're Doing</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/[0.04]">
                    @foreach($form12Subjects->sortByDesc('score') as $sub)
                    @php
                        $sc = $sub['score'];
                        $barColor = $sc >= 65 ? 'bg-blue-500' : ($sc >= 50 ? 'bg-amber-500' : 'bg-rose-400');
                        $isChosen = $topPath && in_array($sub['name'], $topPath['subjects'] ?? []);
                        $status = $sc >= 65 ? 'Strong' : ($sc >= 50 ? 'Okay' : 'Needs Improvement');
                        $statusStyle = $sc >= 65
                            ? 'text-blue-700 bg-blue-50 border-blue-200'
                            : ($sc >= 50 ? 'text-amber-700 bg-amber-50 border-amber-200' : 'text-rose-700 bg-rose-50 border-rose-200');
                    @endphp
                    <tr class="{{ $isChosen ? 'bg-blue-50/40' : 'hover:bg-blue-50/30' }} transition-colors">
                        <td class="px-5 py-3">
                            <p class="font-medium text-black/80">{{ $sub['name'] }}</p>
                            @if($isChosen)
                            <span class="text-[10px] font-bold text-blue-600">★ On your recommended path</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-sm {{ $sc >= 65 ? 'text-blue-600' : ($sc >= 50 ? 'text-amber-600' : 'text-rose-500') }}">
                                    {{ $sc }}%
                                </span>
                                <div class="w-16 h-1.5 bg-black/[0.06] rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $barColor }}" style="width:{{ min($sc, 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded-md border {{ $statusStyle }}">{{ $status }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="p-6">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3">
                <span class="text-xl mt-0.5">📊</span>
                <div>
                    <p class="font-semibold text-amber-800 text-sm">No grades uploaded yet</p>
                    <p class="text-xs text-amber-700 mt-1">Your teacher will upload your results when available. Fill in the form below and the recommendation will be based on your preferences until grades are added.</p>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- 3. Preferred Subjects ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">3</div>
            <div>
                <h2 class="font-bold text-blue-900">Preferred Subjects</h2>
                <p class="text-xs text-blue-600 mt-0.5">Which subjects do you enjoy most? Select all that apply</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach([
                'Mathematics', 'Biology', 'Chemistry', 'Physics',
                'Agriculture', 'ICT', 'English', 'Geography',
                'History', 'Chichewa', 'Business Studies', 'Bible Knowledge'
            ] as $subj)
            <label class="flex items-center gap-2.5 cursor-pointer group p-2 rounded-lg hover:bg-blue-50 transition-colors">
                <input type="checkbox" name="preferred_subjects[]" value="{{ $subj }}"
                       {{ in_array($subj, $savedSubjects) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-blue-200 text-blue-600 accent-blue-600 cursor-pointer">
                <span class="text-sm text-black/70 group-hover:text-blue-800 transition-colors select-none">{{ $subj }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- 4. Career Interests (free text) ────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">4</div>
            <div>
                <h2 class="font-bold text-blue-900">Career Interests</h2>
                <p class="text-xs text-blue-600 mt-0.5">Write the careers you are interested in pursuing</p>
            </div>
        </div>
        <div class="p-6">
            <textarea name="career_interests" rows="4"
                      class="w-full border border-black/10 rounded-xl px-4 py-3 text-sm text-black/75 placeholder-black/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 resize-none"
                      placeholder="E.g. I am interested in becoming a Doctor or a Pharmacist. I have always wanted to help sick people and work in a hospital environment. I am also interested in Engineering and would like to build bridges in my community...">{{ $savedCareersTxt }}</textarea>
            <p class="text-xs text-black/35 mt-1.5">Be specific — mention the careers you want to pursue and why. This helps the system personalise your recommendation.</p>
        </div>
    </div>

    {{-- 5. Personal Interests (free text) ──────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">5</div>
            <div>
                <h2 class="font-bold text-blue-900">Personal Interests &amp; Activities</h2>
                <p class="text-xs text-blue-600 mt-0.5">What do you enjoy doing in your spare time?</p>
            </div>
        </div>
        <div class="p-6">
            <textarea name="personal_interests" rows="4"
                      class="w-full border border-black/10 rounded-xl px-4 py-3 text-sm text-black/75 placeholder-black/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 resize-none"
                      placeholder="E.g. I enjoy solving mathematical problems and conducting science experiments at home. I like reading science books and watching documentaries about nature and technology. I also enjoy helping my neighbours with farming activities...">{{ $savedInterestsTxt }}</textarea>
            <p class="text-xs text-black/35 mt-1.5">Include hobbies, activities you love, and things you are naturally curious about.</p>
        </div>
    </div>

    {{-- 6. Career Goals ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">6</div>
            <div>
                <h2 class="font-bold text-blue-900">Career Goals</h2>
                <p class="text-xs text-blue-600 mt-0.5">Describe what you want to become or achieve in life</p>
            </div>
        </div>
        <div class="p-6">
            <textarea name="career_goals" rows="4"
                      class="w-full border border-black/10 rounded-xl px-4 py-3 text-sm text-black/75 placeholder-black/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 resize-none"
                      placeholder="E.g. I want to become a doctor and serve my community. I am passionate about surgery and would love to study at Kamuzu University of Health Sciences...">{{ $savedGoals }}</textarea>
            <p class="text-xs text-black/35 mt-1.5">Be specific — mention careers, universities, or motivations. This helps the system personalise your recommendation.</p>
        </div>
    </div>

    {{-- 7. Personal Strengths ─────────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">7</div>
            <div>
                <h2 class="font-bold text-blue-900">Personal Strengths</h2>
                <p class="text-xs text-blue-600 mt-0.5">What are you naturally good at? Select all that apply</p>
            </div>
        </div>
        <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-3">
            @foreach([
                'Good at Mathematics', 'Good at Communication',
                'Good at Problem Solving', 'Creative',
                'Leadership Skills', 'Team Player', 'Critical Thinker',
            ] as $strength)
            <label class="flex items-center gap-2.5 cursor-pointer group p-2.5 rounded-lg hover:bg-blue-50 border border-transparent hover:border-blue-100 transition-all">
                <input type="checkbox" name="strengths[]" value="{{ $strength }}"
                       {{ in_array($strength, $savedStrengths) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-blue-200 text-blue-600 accent-blue-600 cursor-pointer">
                <span class="text-sm text-black/70 group-hover:text-blue-800 transition-colors select-none">{{ $strength }}</span>
            </label>
            @endforeach
        </div>
    </div>

    {{-- 8. Learning Preference (free text) ──────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">8</div>
            <div>
                <h2 class="font-bold text-blue-900">How Do You Learn Best?</h2>
                <p class="text-xs text-blue-600 mt-0.5">Describe the way you learn most effectively</p>
            </div>
        </div>
        <div class="p-6">
            <textarea name="learning_preference" rows="3"
                      class="w-full border border-black/10 rounded-xl px-4 py-3 text-sm text-black/75 placeholder-black/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 resize-none"
                      placeholder="E.g. I learn best through practical activities and laboratory experiments. I enjoy hands-on work and find it easier to understand concepts when I can see them in action. I also like working in groups and discussing topics with classmates...">{{ $savedLearning }}</textarea>
        </div>
    </div>

    {{-- 9. Additional Information ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-blue-50 border-b border-blue-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-black shrink-0">9</div>
            <div>
                <h2 class="font-bold text-blue-900">Additional Information</h2>
                <p class="text-xs text-blue-600 mt-0.5">Anything else that should be considered in your recommendation? (optional)</p>
            </div>
        </div>
        <div class="p-6">
            <textarea name="additional_info" rows="3"
                      class="w-full border border-black/10 rounded-xl px-4 py-3 text-sm text-black/75 placeholder-black/30 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-400 resize-none"
                      placeholder="E.g. I have a health condition... / My family is in farming... / I prefer to study near my home district...">{{ $savedAdditional }}</textarea>
        </div>
    </div>

    {{-- 10. Declaration + Actions ─────────────────────────────────────── --}}
    <div class="bg-white rounded-2xl border border-blue-200 shadow-sm p-6">
        <div class="flex items-start gap-3 mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <input type="checkbox" name="declaration" value="1" id="declaration" required
                   class="mt-0.5 w-4 h-4 rounded border-blue-300 text-blue-600 accent-blue-600 cursor-pointer shrink-0">
            <label for="declaration" class="text-sm text-black/70 leading-relaxed cursor-pointer">
                I confirm that the information I have provided above is accurate and honest. I understand that this recommendation is a guide — final subject selection must be confirmed with my class teacher and school administration.
            </label>
        </div>

        <div class="flex flex-col sm:flex-row gap-3">
            <button type="submit" name="action" value="save"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-white border-2 border-blue-200 text-blue-700 hover:border-blue-400 hover:bg-blue-50 rounded-xl font-bold text-sm shadow-sm hover:shadow-md transition-all">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                Save My Profile
            </button>
            <button type="submit" name="action" value="analyze"
                    class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-8 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold text-sm shadow-sm hover:shadow-md transition-all">
                Analyse Subject Combination
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </button>
        </div>

        @if($prefsComplete)
        <p class="text-xs text-blue-600 mt-3 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            Profile previously saved — {{ $analysis?->updated_at?->diffForHumans() ?? 'recently' }}
        </p>
        @endif
    </div>

    </div>{{-- end space-y-5 form sections --}}
</form>

@if($analyzed ?? false)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var el = document.getElementById('recommendation');
        if (el) { setTimeout(function(){ el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }, 300); }
    });
</script>
@endif

{{-- ═══════════════════════════════════════════════════════════════════════ --}}
{{-- RECOMMENDATION RESULTS (shown below the form when data is ready)       --}}
{{-- ═══════════════════════════════════════════════════════════════════════ --}}
<div id="recommendation">

@if($isEarlyForm)
{{-- ── Form 1/2 Gate ─────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border-2 border-amber-200 shadow-sm overflow-hidden">
    <div class="px-6 py-5 bg-amber-50 border-b border-amber-200">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-xl shrink-0">📚</div>
            <div>
                <h2 class="font-bold text-amber-900">You are in {{ $currentForm }}</h2>
                <p class="text-sm text-amber-700">Subject combinations are selected in Form 3 and Form 4 only.</p>
            </div>
        </div>
    </div>
    <div class="p-6 space-y-4">
        <p class="text-sm text-black/70 leading-relaxed">
            In <strong>{{ $currentForm }}</strong>, all students study the same core subjects. Focus on performing well now — your grades will directly influence which combination is recommended when you reach Form 3.
        </p>
        @php
            $formOrder = ['Form 1' => 1, 'Form 2' => 2, 'Form 3' => 3, 'Form 4' => 4];
            $currentN  = $formOrder[$currentForm] ?? 1;
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            @foreach($formOrder as $f => $n)
            @php $isCurrent = $f === $currentForm; @endphp
            <div class="rounded-xl p-3 text-center border {{ $isCurrent ? 'bg-blue-600 border-blue-600' : ($n < $currentN ? 'bg-blue-50 border-blue-200' : 'bg-slate-50 border-black/[0.06]') }}">
                <p class="text-xs font-bold {{ $isCurrent ? 'text-white/80' : ($n < $currentN ? 'text-blue-700' : 'text-black/40') }}">{{ $f }}</p>
                <p class="text-xs mt-0.5 {{ $isCurrent ? 'text-white font-bold' : ($n < $currentN ? 'text-blue-600' : 'text-black/30') }}">
                    @if($isCurrent) ← You are here
                    @elseif($n < $currentN) Completed
                    @elseif($f === 'Form 3') Choose Combination
                    @else MSCE Exams
                    @endif
                </p>
            </div>
            @endforeach
        </div>
        @if($hasgrades)
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm font-bold text-blue-800 mb-1">✓ Your teacher has uploaded your grades</p>
            <p class="text-sm text-blue-700">Keep performing well. The system is tracking your progress for Form 3.</p>
        </div>
        @endif
    </div>
</div>

@elseif(!$topPath || (!$hasgrades && !$hasAssmt))
{{-- ── No data yet ──────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-dashed border-blue-200 p-10 text-center shadow-sm">
    <p class="text-5xl mb-4">📋</p>
    <h3 class="font-bold text-black/80 text-xl mb-2">Fill in Your Details Above</h3>
    <p class="text-sm text-black/50 mb-6 max-w-md mx-auto">Complete the form above and click <strong>Analyse Subject Combination</strong> to receive your personalised recommendation.</p>
    <div class="flex flex-wrap gap-3 justify-center">
        <a href="{{ route('student.assessment') }}"
           class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-blue-200 text-blue-700 rounded-xl font-semibold text-sm hover:bg-blue-50">
            Take Self Assessment
        </a>
    </div>
</div>

@else
{{-- ── Full recommendation results ──────────────────────────────────────── --}}

{{-- ─ 1. Main Recommendation Banner ────────────────────────────────────── --}}
<div class="bg-white rounded-2xl shadow-sm overflow-hidden border-2 border-blue-600">
    <div class="px-6 py-7 text-center text-white" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
        <p class="text-[11px] font-bold uppercase tracking-widest opacity-75 mb-2">🎓 Subject Combination Recommendation</p>
        <h2 class="text-3xl font-black tracking-wide">{{ strtoupper($shortName($topPath['path'])) }} PATH</h2>
        <p class="text-white/60 text-xs mt-1 uppercase font-semibold tracking-wider">Confidence Score: {{ round($topPath['score'] ?? 0) }}%</p>
    </div>
    <div class="p-6 space-y-5">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-2.5">
                <span class="w-3.5 h-3.5 rounded-full shrink-0
                    {{ $topPath['status'] === 'ready' ? 'bg-blue-500' : ($topPath['status'] === 'partial' ? 'bg-amber-500' : 'bg-slate-400') }}"></span>
                <span class="font-bold text-base
                    {{ $topPath['status'] === 'ready' ? 'text-blue-700' : ($topPath['status'] === 'partial' ? 'text-amber-700' : 'text-slate-600') }}">
                    Recommended Pathway: {{ $shortName($topPath['path']) }}
                </span>
            </div>
            <div class="text-right">
                <p class="text-[10px] text-black/35 font-bold uppercase">Confidence Score</p>
                <p class="text-3xl font-black text-blue-600">{{ round($topPath['score'] ?? 0) }}%</p>
            </div>
        </div>

        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
            <p class="text-sm text-blue-800 leading-relaxed">
                🎉 <strong>Congratulations!</strong> Based on your academic performance, interests, career goals, and personal strengths,
                the <strong>{{ $shortName($topPath['path']) }}</strong> pathway is the most suitable option for you.
            </p>
        </div>

        <div>
            <p class="text-xs font-bold text-black/40 uppercase mb-3">Recommended Subject Combination</p>
            <div class="rounded-xl border border-blue-100 overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-blue-50 border-b border-blue-100">
                        <tr>
                            <th class="px-4 py-2.5 text-left text-xs font-bold text-blue-700 uppercase">Subject</th>
                            <th class="px-4 py-2.5 text-right text-xs font-bold text-blue-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-blue-50/60">
                        @foreach($topPath['subjects'] as $sub)
                        <tr class="hover:bg-blue-50/40 transition-colors">
                            <td class="px-4 py-3 font-semibold text-black/80">{{ $sub }}</td>
                            <td class="px-4 py-3 text-right font-bold text-blue-600 text-sm">✅ Recommended</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(!empty($topPath['not_ready_reasons']))
        <div class="rounded-xl bg-amber-50 border border-amber-200 p-4">
            <p class="text-xs font-bold text-amber-700 uppercase mb-2">⚠ Subjects to Improve Before Selecting</p>
            <ul class="space-y-1">
                @foreach($topPath['not_ready_reasons'] as $reason)
                <li class="text-sm text-amber-800">• {{ $reason }}</li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>

{{-- ─ 2. Why This Pathway Was Recommended ──────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-6">
    <h3 class="font-bold text-black/80 mb-1">Why This Pathway Was Recommended</h3>
    <p class="text-xs text-black/45 mb-4">Your recommendation is based on the following factors:</p>
    <ul class="space-y-3">
        @if(!empty($topPath['why_reasons']))
            @foreach($topPath['why_reasons'] as $reason)
            <li class="flex items-start gap-3 text-sm text-black/70">
                <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                {{ $reason }}
            </li>
            @endforeach
        @else
        @php
            $autoReasons = [
                'You have strong academic performance in ' . $shortName($topPath['path']) . ' related subjects.',
                'Your career interests align with careers requiring the ' . $shortName($topPath['path']) . ' pathway.',
                'Your personal strengths match the skills required for this subject combination.',
            ];
            if($prefsComplete) $autoReasons[] = 'Your preference profile confirms a strong fit for this pathway.';
            if($hasgrades) $autoReasons[] = 'Your teacher-uploaded grades support this recommendation with ' . $confLabel . ' confidence.';
        @endphp
        @foreach($autoReasons as $reason)
        <li class="flex items-start gap-3 text-sm text-black/70">
            <svg class="w-4 h-4 text-blue-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ $reason }}
        </li>
        @endforeach
        @endif
    </ul>
</div>

{{-- ─ 3. Subject Readiness ──────────────────────────────────────────────── --}}
@if($totalSubs > 0)
<div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-black/[0.06]">
        <h3 class="font-bold text-black/80">Subject Readiness</h3>
        <p class="text-xs text-black/45 mt-0.5">Your current score for each required subject (pass mark: 55%)</p>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-blue-50/50 border-b border-blue-100">
            <tr>
                <th class="px-5 py-3 text-left text-xs font-bold text-black/40 uppercase">Subject</th>
                <th class="px-5 py-3 text-left text-xs font-bold text-black/40 uppercase">Readiness</th>
                <th class="px-5 py-3 text-right text-xs font-bold text-black/40 uppercase">Score</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-black/[0.04]">
            @foreach($topPath['subjects'] as $sub)
            @php
                $gs   = $topPath['grade_scores'][$sub] ?? null;
                $rdy  = $gs !== null && $gs >= 55;
                $none = $gs === null;
            @endphp
            <tr class="hover:bg-blue-50/30">
                <td class="px-5 py-3 font-medium text-black/75">{{ $sub }}</td>
                <td class="px-5 py-3">
                    @if($gs !== null)
                    <div class="flex items-center gap-2">
                        <div class="w-24 h-2 bg-black/[0.06] rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $rdy ? 'bg-blue-500' : 'bg-rose-400' }}" style="width:{{ min($gs,100) }}%"></div>
                        </div>
                        <span class="text-xs font-bold {{ $rdy ? 'text-blue-500' : 'text-rose-400' }}">{{ $rdy ? '✓' : '✗' }}</span>
                    </div>
                    @else
                    <span class="text-xs text-black/30 italic">No grade yet</span>
                    @endif
                </td>
                <td class="px-5 py-3 text-right font-black {{ $none ? 'text-black/25' : ($rdy ? 'text-blue-600' : 'text-rose-500') }}">
                    {{ $gs !== null ? $gs . '%' : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="px-5 py-3 bg-blue-50/30 border-t border-blue-100">
        <div class="flex items-center justify-between text-xs text-black/45 mb-1.5">
            <span>Overall Readiness</span>
            <span>{{ $readyCount }}/{{ $totalSubs }} subjects at pass mark · <strong>{{ $readyPct }}%</strong></span>
        </div>
        <div class="h-2.5 bg-black/[0.06] rounded-full overflow-hidden">
            <div class="h-full rounded-full {{ $readyPct >= 75 ? 'bg-blue-500' : ($readyPct >= 50 ? 'bg-amber-500' : 'bg-rose-400') }}"
                 style="width:{{ $readyPct }}%"></div>
        </div>
    </div>
</div>
@endif

{{-- ─ 4. Career Opportunities ──────────────────────────────────────────── --}}
@php
    $pathCareers = \App\Models\Career::where('category', 'like', '%'.str_replace(' Path','',$topPath['path'] ?? '').'%')
        ->orWhere('title', 'like', '%'.($topPath['path'] === 'Science Path' ? 'Doctor' : ($topPath['path'] === 'Humanities Path' ? 'Teacher' : 'Business')).'%')
        ->take(6)->get();
    if($pathCareers->isEmpty()) $pathCareers = \App\Models\Career::take(6)->get();
    $emojiMap = ['Doctor' => '👨‍⚕️', 'Pharmacist' => '💊', 'Clinical' => '🩺', 'Nurse' => '🩺', 'Engineer' => '⚙️', 'Software' => '💻', 'Agricultural' => '🌱', 'Agric' => '🌱', 'Laboratory' => '🔬', 'Lab' => '🔬', 'Teacher' => '📚', 'Lawyer' => '⚖️', 'Journalist' => '📰', 'Business' => '💼', 'Mechanic' => '🔧', 'Environ' => '🌿'];
@endphp
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h3 class="font-bold text-black/80 mb-1">Career Opportunities</h3>
    <p class="text-xs text-black/45 mb-4">With the <strong>{{ $shortName($topPath['path']) }}</strong> combination, you will be well prepared to pursue careers such as:</p>
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
        @foreach($pathCareers as $c)
        @php $em = '🎓'; foreach($emojiMap as $kw => $icon) { if(str_contains($c->title, $kw)) { $em = $icon; break; } } @endphp
        <div class="flex items-center gap-2.5 p-3 rounded-xl bg-blue-50 border border-blue-100 hover:bg-blue-100 transition-colors">
            <span class="text-xl shrink-0">{{ $em }}</span>
            <span class="text-sm font-semibold text-black/80">{{ $c->title }}</span>
        </div>
        @endforeach
    </div>
    <div class="mt-3 text-right">
        <a href="{{ route('student.recommendations') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700">View All Career Recommendations →</a>
    </div>
</div>

{{-- ─ 5. Alternative Pathway ────────────────────────────────────────────── --}}
@if(count($mscePaths) > 1)
@php
    $altPath   = $mscePaths[1];
    $altRating = $ratings[1] ?? ['stars' => 2, 'empty' => 3, 'label' => 'Possible Match'];
    $altCareers = \App\Models\Career::where('category', 'like', '%'.str_replace(' Path','',$altPath['path'] ?? '').'%')->take(4)->get();
    if($altCareers->isEmpty()) $altCareers = \App\Models\Career::skip(4)->take(4)->get();
@endphp
<div class="bg-white rounded-2xl border border-black/8 shadow-sm overflow-hidden">
    <div class="px-5 py-4 border-b border-black/[0.06] bg-amber-50/70">
        <h3 class="font-bold text-amber-900">Alternative Pathway</h3>
        <p class="text-xs text-amber-700 mt-0.5">If you decide not to follow the {{ $shortName($topPath['path']) }} pathway, your next best option is:</p>
    </div>
    <div class="p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h4 class="font-black text-black/80 text-xl">{{ $shortName($altPath['path']) }}</h4>
                <p class="text-sm font-bold text-amber-600">{{ round($altPath['score']) }}% Match</p>
            </div>
            <div class="flex gap-0.5">
                @for($i=0;$i<$altRating['stars'];$i++)<span class="text-amber-400 text-xl">★</span>@endfor
                @for($i=0;$i<$altRating['empty'];$i++)<span class="text-black/15 text-xl">★</span>@endfor
            </div>
        </div>
        <div class="grid sm:grid-cols-2 gap-5">
            <div>
                <p class="text-xs font-bold text-black/40 uppercase mb-2.5">Recommended Subjects</p>
                <ul class="space-y-2">
                    @foreach(array_slice($altPath['subjects'] ?? [], 0, 5) as $sub)
                    <li class="flex items-center gap-2 text-sm text-black/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>{{ $sub }}
                    </li>
                    @endforeach
                </ul>
            </div>
            <div>
                <p class="text-xs font-bold text-black/40 uppercase mb-2.5">Possible Careers</p>
                <ul class="space-y-2">
                    @foreach($altCareers as $ac)
                    <li class="flex items-center gap-2 text-sm text-black/70">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>{{ $ac->title }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
</div>
@endif


{{-- ─ 7. Areas for Improvement ─────────────────────────────────────────── --}}
@php $weakSubs2 = $form12Subjects->where('score', '<', 75)->sortBy('score')->take(3); @endphp
@if($weakSubs2->isNotEmpty())
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h3 class="font-bold text-black/80 mb-1">Areas for Improvement</h3>
    <p class="text-xs text-black/45 mb-4">To strengthen your chances of succeeding in the {{ $shortName($topPath['path']) }} pathway:</p>
    <div class="space-y-3">
        @foreach($weakSubs2 as $ws)
        @php $target = max(75, min(100, $ws['score'] + max(7, intval((75 - $ws['score']) * 0.5)))); @endphp
        <div class="flex items-start gap-3 p-4 rounded-xl bg-rose-50 border border-rose-100">
            <div class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-rose-800 text-sm">Improve {{ $ws['name'] }} from {{ $ws['score'] }}% to at least {{ $target }}%</p>
                <p class="text-xs text-rose-600 mt-0.5">{{ round($target - $ws['score']) }} marks needed to reach the target</p>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ─ 8. Guidance Summary ───────────────────────────────────────────────── --}}
<div class="rounded-2xl p-6 text-white" style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
    <div class="flex items-center gap-2 mb-3">
        <span class="text-xl">🎓</span>
        <h3 class="font-bold text-white">Guidance Summary</h3>
    </div>
    <p class="text-sm text-white/90 leading-relaxed">
        You are currently <strong class="text-white">{{ $topPath['status'] === 'ready' ? 'on track' : 'making progress' }}</strong> for the
        <strong class="text-white">{{ $topPath['path'] }}</strong>.
        Your academic performance, interests, and career aspirations
        {{ $topPath['status'] === 'ready' ? 'strongly indicate' : 'suggest' }} that this pathway
        matches your abilities and future goals.
        Continue working hard in your core {{ $shortName($topPath['path']) }} subjects to increase your
        opportunities for university admission and future careers.
    </p>
</div>

{{-- ─ 9. Next Steps ────────────────────────────────────────────────────── --}}
<div class="bg-white rounded-2xl border border-black/8 shadow-sm p-5">
    <h3 class="font-bold text-black/80 mb-4">Your Next Steps</h3>
    <div class="space-y-3">
        @foreach([
            ['icon' => '💬', 'step' => 'Discuss this recommendation with your parent or guardian.'],
            ['icon' => '👨‍🏫', 'step' => 'Share the result with your class teacher for their input.'],
            ['icon' => '📅', 'step' => 'Book a counselling session to get your combination reviewed and approved.'],
            ['icon' => '✅', 'step' => 'Officially select ' . $shortName($topPath['path']) . ' Combination when school registration opens.'],
            ['icon' => '📈', 'step' => 'Keep improving weak subjects to increase your readiness score.'],
        ] as $n => $item)
        <div class="flex items-start gap-3">
            <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-xs font-black shrink-0 mt-0.5">{{ $n + 1 }}</div>
            <p class="text-sm text-black/70 pt-1.5">{{ $item['step'] }}</p>
        </div>
        @endforeach
    </div>
</div>

{{-- ─ 10. CTA Row ───────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div class="rounded-2xl bg-blue-50 border border-blue-100 p-5 flex flex-col">
        <p class="font-bold text-blue-800 text-sm">Book a Counsellor</p>
        <p class="text-xs text-blue-600/70 mt-1 flex-1">Get your combination reviewed and approved by a career guide.</p>
        <a href="{{ route('student.appointments') }}" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700">
            Book Appointment →
        </a>
    </div>
    <div class="rounded-2xl bg-blue-50 border border-blue-100 p-5 flex flex-col">
        <p class="font-bold text-blue-800 text-sm">Update My Profile</p>
        <p class="text-xs text-blue-600/70 mt-1 flex-1">Changed your interests? Scroll up and update your details to refresh the recommendation.</p>
        <a href="#" onclick="window.scrollTo({top:0,behavior:'smooth'})" class="mt-4 inline-flex items-center gap-1.5 text-xs font-bold text-blue-600 hover:text-blue-700">
            Update Details ↑
        </a>
    </div>
</div>

@endif {{-- end full results --}}

</div>{{-- end #recommendation --}}

</div>{{-- end main space-y-5 --}}

@push('scripts')
<script>
// After "Analyse Subject Combination" is submitted, auto-scroll to the recommendation section
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    if (params.has('analyzed')) {
        const el = document.getElementById('recommendation');
        if (el) {
            setTimeout(function () {
                el.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }, 300);
        }
        // Clean URL without reload
        const clean = window.location.pathname;
        window.history.replaceState({}, '', clean);
    }
});
</script>
@endpush

@endsection
