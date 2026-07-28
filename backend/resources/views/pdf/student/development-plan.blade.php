<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Personal Development Plan — {{ $user->name }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.7; color: #000000; margin: 0; padding: 20px; }
.header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #2563eb; padding-bottom: 16px; }
.header h1 { color: #1e40af; margin: 0; font-size: 22px; font-weight: bold; }
.header h2 { color: #2563eb; margin: 6px 0 0; font-size: 16px; }
.header p { margin: 4px 0 0; color: #000000; font-size: 11px; }
.student-info { background: #eff6ff; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
.info-row { display: inline-block; width: 48%; margin-bottom: 4px; }
.label { font-weight: bold; color: #374151; display: inline-block; width: 110px; }
.value { color: #1e40af; }
.section-title { color: #1e40af; font-size: 14px; font-weight: bold; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #2563eb; }
.goal-card { border: 1px solid #dbeafe; border-radius: 6px; padding: 14px; margin-bottom: 12px; page-break-inside: avoid; }
.goal-number { display: inline-block; background: #2563eb; color: #ffffff; width: 22px; height: 22px; border-radius: 50%; text-align: center; line-height: 22px; font-size: 11px; font-weight: bold; margin-right: 8px; }
.goal-title { font-size: 13px; font-weight: bold; color: #1e40af; }
.score-badge { background: #dbeafe; color: #1d4ed8; font-weight: bold; padding: 2px 8px; border-radius: 10px; font-size: 11px; float: right; }
.steps-list { margin: 8px 0 0 30px; padding: 0; }
.steps-list li { margin-bottom: 4px; font-size: 11px; color: #000000; }
.skills-needed { margin-top: 8px; font-size: 11px; color: #000000; }
.skills-needed strong { color: #000000; }
.subjects-needed { margin-top: 6px; font-size: 11px; }
.subject-chip { display: inline-block; background: #dbeafe; color: #000000; border: 1px solid #dbeafe; padding: 2px 8px; border-radius: 10px; font-size: 10px; margin: 2px 2px 2px 0; }
.academic-summary { margin-bottom: 8px; font-size: 11px; }
.weak-subject { background: #dbeafe; border-left: 3px solid #2563eb; padding: 5px 10px; margin-bottom: 4px; border-radius: 0 4px 4px 0; }
.strong-subject { background: #eff6ff; border-left: 3px solid #2563eb; padding: 5px 10px; margin-bottom: 4px; border-radius: 0 4px 4px 0; }
.action-box { background: #eff6ff; border: 1px solid #dbeafe; border-radius: 6px; padding: 12px 16px; margin-top: 16px; }
.action-title { font-weight: bold; color: #1d4ed8; font-size: 12px; margin-bottom: 8px; }
.action-item { padding: 4px 0; font-size: 11px; color: #1e40af; }
.action-item::before { content: "✓ "; color: #2563eb; font-weight: bold; }
.footer { text-align: center; font-size: 10px; color: #000000; margin-top: 30px; border-top: 1px solid #dbeafe; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>Personal Development Plan</h2>
    <p>Smart Career &amp; Subject Guidance Tool &mdash; Generated {{ now()->format('F d, Y') }}</p>
</div>

<div class="student-info">
    <div class="info-row"><span class="label">Student:</span><span class="value">{{ $user->name }}</span></div>
    <div class="info-row"><span class="label">Student No.:</span><span class="value">{{ $user->studentProfile?->student_number ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Form Level:</span><span class="value">{{ $profile?->form_level ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Stream:</span><span class="value">{{ $profile?->stream ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Interests:</span><span class="value">{{ $profile?->interests ?? 'Not specified' }}</span></div>
    <div class="info-row"><span class="label">Plan Date:</span><span class="value">{{ now()->format('d M Y') }}</span></div>
</div>

<div class="section-title">Career Goals</div>

@if($topCareers->isEmpty())
<p style="color:#000000; text-align:center;">Complete the career assessment to set your career goals.</p>
@else
@foreach($topCareers as $i => $rec)
@php
    $career = $rec->recommended;
    $score  = (int) $rec->confidence_score;
    $skills = $career?->required_skills ? array_slice(explode(',', $career->required_skills), 0, 5) : [];
    $reqSubs = $career?->requiredSubjects ?? collect();
@endphp
@if($career)
<div class="goal-card">
    <span class="score-badge">{{ $score }}% match</span>
    <span class="goal-number">{{ $i + 1 }}</span>
    <span class="goal-title">{{ $career->title }}</span>
    <div style="font-size:10px; color:#000000; margin: 2px 0 8px 30px;">{{ $career->category }}</div>

    <div class="steps-list" style="list-style:none; margin-left:0; padding-left:0;">
        @if(!empty($skills))
        <div class="skills-needed"><strong>Skills to develop:</strong> {{ implode(', ', array_map('trim', $skills)) }}</div>
        @endif
        @if($reqSubs->isNotEmpty())
        <div class="subjects-needed">
            <strong>Required subjects:</strong>
            @foreach($reqSubs as $sub)
            <span class="subject-chip">{{ $sub->name }}</span>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endif
@endforeach
@endif

<div class="section-title">Academic Action Points</div>

@php
    $subjectAvgs = $academicResults->groupBy('subject.name')
        ->map(fn($g) => round($g->avg('score'), 1));
    $weakSubjects   = $subjectAvgs->filter(fn($v) => $v < 50)->sortBy(fn($v) => $v);
    $strongSubjects = $subjectAvgs->filter(fn($v) => $v >= 70)->sortByDesc(fn($v) => $v);
@endphp

@if($academicResults->isEmpty())
<p style="color:#000000; text-align:center;">No academic results recorded yet.</p>
@else
@if($weakSubjects->isNotEmpty())
<div style="margin-bottom:8px; font-weight:bold; font-size:11px; color:#1d4ed8;">Areas needing improvement (below 50%):</div>
@foreach($weakSubjects as $subName => $avg)
<div class="weak-subject">{{ $subName }} &mdash; {{ $avg }}% (focus here first)</div>
@endforeach
@endif

@if($strongSubjects->isNotEmpty())
<div style="margin-bottom:8px; margin-top:12px; font-weight:bold; font-size:11px; color:#1e3a8a;">Your strongest subjects (70%+):</div>
@foreach($strongSubjects as $subName => $avg)
<div class="strong-subject">{{ $subName }} &mdash; {{ $avg }}%</div>
@endforeach
@endif
@endif

<div class="action-box">
    <div class="action-title">Recommended Next Steps</div>
    <div class="action-item">Review your top career match and research what it involves daily</div>
    <div class="action-item">Focus extra study time on subjects below 50% — ask your teacher for help</div>
    <div class="action-item">Confirm your subject combination aligns with your career goals</div>
    <div class="action-item">Speak with your school counsellor about university eligibility and entry requirements</div>
    <div class="action-item">Re-take the career assessment next term to track how your interests develop</div>
</div>

<div class="footer">
    <p>This Personal Development Plan is auto-generated by the Smart Career &amp; Subject Guidance Tool.</p>
    <p>Review this plan with your school counsellor. Luwinga Secondary School, Mzuzu City, Malawi.</p>
</div>
</body>
</html>
