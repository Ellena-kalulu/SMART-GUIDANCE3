<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Assessment Summary — {{ $user->name }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.6; color: #000000; margin: 0; padding: 20px; }
.header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #2563eb; padding-bottom: 16px; }
.header h1 { color: #1e40af; margin: 0; font-size: 22px; font-weight: bold; }
.header h2 { color: #2563eb; margin: 6px 0 0; font-size: 16px; }
.header p { margin: 4px 0 0; color: #000000; font-size: 11px; }
.student-info { background: #eff6ff; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
.info-row { display: inline-block; width: 48%; margin-bottom: 4px; }
.label { font-weight: bold; color: #374151; display: inline-block; width: 120px; }
.value { color: #1e40af; }
.section-title { color: #1e40af; font-size: 14px; font-weight: bold; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #2563eb; }
.category-item { padding: 8px 12px; margin-bottom: 6px; border-radius: 4px; background: #eff6ff; border: 1px solid #dbeafe; }
.cat-name { font-weight: bold; color: #1e40af; display: inline-block; width: 200px; }
.cat-count { color: #2563eb; font-weight: bold; }
.cat-bar-bg { display: inline-block; width: 200px; height: 10px; background: #dbeafe; border-radius: 5px; vertical-align: middle; margin-left: 8px; }
.cat-bar-fill { height: 10px; border-radius: 5px; background: #3b82f6; display: inline-block; }
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th { background-color: #2563eb; color: #ffffff; padding: 7px 10px; text-align: left; font-size: 11px; }
td { border: 1px solid #dbeafe; padding: 7px 10px; font-size: 11px; vertical-align: top; }
tr:nth-child(even) td { background-color: #eff6ff; }
.q-number { color: #000000; font-size: 10px; }
.highlight-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 14px; border-radius: 4px; margin-bottom: 14px; }
.footer { text-align: center; font-size: 10px; color: #000000; margin-top: 30px; border-top: 1px solid #dbeafe; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>Assessment Summary Report</h2>
    <p>Smart Career &amp; Subject Guidance Tool &mdash; Generated {{ now()->format('F d, Y') }}</p>
</div>

<div class="student-info">
    <div class="info-row"><span class="label">Student:</span><span class="value">{{ $user->name }}</span></div>
    <div class="info-row"><span class="label">Student No.:</span><span class="value">{{ $user->studentProfile?->student_number ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Form Level:</span><span class="value">{{ $user->studentProfile?->form_level ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Completed:</span><span class="value">{{ $attempt->completed_at?->format('d M Y, H:i') ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Questions:</span><span class="value">{{ $attempt->responses->count() }} answered</span></div>
    <div class="info-row"><span class="label">Report Date:</span><span class="value">{{ now()->format('d M Y') }}</span></div>
</div>

@if(!empty($categoryTally))
<div class="section-title">Career Interest Category Scores</div>
<div class="highlight-box">
    Your responses mapped to these career interest categories. The category with the highest count best reflects your natural inclinations.
</div>
@php $maxCount = max($categoryTally); @endphp
@foreach($categoryTally as $category => $count)
@php $pct = $maxCount > 0 ? round(($count / $maxCount) * 100) : 0; @endphp
<div class="category-item">
    <span class="cat-name">{{ $category }}</span>
    <span class="cat-count">{{ $count }} point(s)</span>
    <span class="cat-bar-bg">
        <span class="cat-bar-fill" style="width: {{ $pct }}%;"></span>
    </span>
</div>
@endforeach
@php $topCategory = array_key_first($categoryTally); @endphp
<div style="margin-top:8px; font-size:11px; color:#1e40af; background:#eff6ff; border-left:4px solid #2563eb; padding:8px 12px; border-radius:4px;">
    <strong>Top Interest Area:</strong> {{ $topCategory }} — This is the career field your responses aligned with most strongly.
</div>
@endif

<div class="section-title">Question-by-Question Responses</div>
<table>
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:45%">Question</th>
            <th style="width:30%">Your Answer</th>
            <th style="width:20%">Interest Area</th>
        </tr>
    </thead>
    <tbody>
        @foreach($attempt->responses as $i => $response)
        <tr>
            <td class="q-number" style="text-align:center;">{{ $i + 1 }}</td>
            <td>{{ $response->question?->text ?? '—' }}</td>
            <td>{{ $response->option?->text ?? $response->text_response ?? '—' }}</td>
            <td style="color:#2563eb; font-size:10px;">{{ $response->option?->mapped_career_category ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <p>This assessment summary is auto-generated by the Smart Career &amp; Subject Guidance Tool.</p>
    <p>Luwinga Secondary School, Mzuzu City, Malawi &mdash; Discuss your results with your school counsellor.</p>
</div>
</body>
</html>
