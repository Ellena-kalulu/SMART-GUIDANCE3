<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Academic Progress Report — {{ $user->name }}</title>
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
.term-heading { background: #1e40af; color: #ffffff; padding: 8px 12px; border-radius: 4px 4px 0 0; font-weight: bold; font-size: 12px; margin-top: 16px; }
.term-avg { float: right; font-size: 11px; font-weight: normal; }
table { width: 100%; border-collapse: collapse; margin-bottom: 4px; page-break-inside: avoid; }
th { background-color: #2563eb; color: #ffffff; padding: 7px 10px; text-align: left; font-size: 11px; }
td { border: 1px solid #dbeafe; padding: 7px 10px; font-size: 11px; }
tr:nth-child(even) td { background-color: #eff6ff; }
.grade-A { color: #2563eb; font-weight: bold; }
.grade-B { color: #2563eb; font-weight: bold; }
.grade-C { color: #000000; font-weight: bold; }
.grade-D { color: #000000; font-weight: bold; }
.grade-F { color: #2563eb; font-weight: bold; }
.avg-row td { background: #dbeafe; font-weight: bold; }
.summary-box { background: #eff6ff; border-left: 4px solid #2563eb; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 11px; }
.footer { text-align: center; font-size: 10px; color: #000000; margin-top: 30px; border-top: 1px solid #dbeafe; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>Academic Progress Report</h2>
    <p>Smart Career &amp; Subject Guidance Tool &mdash; Generated {{ now()->format('F d, Y') }}</p>
</div>

<div class="student-info">
    <div class="info-row"><span class="label">Student:</span><span class="value">{{ $user->name }}</span></div>
    <div class="info-row"><span class="label">Student No.:</span><span class="value">{{ $user->studentProfile?->student_number ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Form Level:</span><span class="value">{{ $profile?->form_level ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Stream:</span><span class="value">{{ $profile?->stream ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">School:</span><span class="value">{{ $profile?->school ?? 'Luwinga Secondary School' }}</span></div>
    <div class="info-row"><span class="label">Report Date:</span><span class="value">{{ now()->format('d M Y') }}</span></div>
</div>

@if($academicResults->isEmpty())
<div style="text-align:center; padding:30px; color:#000000;">No academic results recorded yet. Results will appear here once your teacher enters them.</div>
@else

@php
    $overallAvg = round($academicResults->avg('score'), 1);
    $overallGrade = $overallAvg >= 80 ? 'A' : ($overallAvg >= 65 ? 'B' : ($overallAvg >= 50 ? 'C' : ($overallAvg >= 40 ? 'D' : 'F')));
@endphp
<div class="summary-box">
    <strong>Overall Performance:</strong>
    Average Score: <strong>{{ $overallAvg }}%</strong> &mdash;
    Overall Grade: <strong class="grade-{{ $overallGrade }}">{{ $overallGrade }}</strong> &mdash;
    Total Subjects: <strong>{{ $academicResults->pluck('subject.name')->unique()->count() }}</strong> &mdash;
    Terms Recorded: <strong>{{ $resultsByTerm->count() }}</strong>
</div>

<div class="section-title">Results by Term</div>

@foreach($resultsByTerm as $term => $results)
@php $termAvg = round($results->avg('score'), 1); @endphp
<div class="term-heading">
    Term {{ $term }}
    <span class="term-avg">Average: {{ $termAvg }}%</span>
</div>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Score (%)</th>
            <th>Grade</th>
            <th>Teacher Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results as $result)
        @php
            $s = $result->score;
            $g = $s >= 80 ? 'A' : ($s >= 65 ? 'B' : ($s >= 50 ? 'C' : ($s >= 40 ? 'D' : 'F')));
        @endphp
        <tr>
            <td>{{ $result->subject?->name ?? 'N/A' }}</td>
            <td><strong class="grade-{{ $g }}">{{ $s }}%</strong></td>
            <td class="grade-{{ $g }}">{{ $g }}</td>
            <td style="color:#000000;">{{ $result->teacher_remarks ?? '—' }}</td>
        </tr>
        @endforeach
        <tr class="avg-row">
            <td>Term Average</td>
            <td colspan="3">{{ $termAvg }}%</td>
        </tr>
    </tbody>
</table>
@endforeach

@endif

<div class="footer">
    <p>This report is auto-generated by the Smart Career &amp; Subject Guidance Tool.</p>
    <p>Luwinga Secondary School, Mzuzu City, Malawi &mdash; For guidance, speak with your class teacher or school counsellor.</p>
</div>
</body>
</html>
