<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Progress Report - {{ $child->name }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.5; color: #111; }
        .header { text-align: center; border-bottom: 3px solid #2563eb; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #1e40af; font-size: 20px; margin: 0 0 4px; }
        .header h2 { color: #2563eb; font-size: 16px; margin: 4px 0; }
        .header p  { color: #6b7280; font-size: 10px; margin: 0; }
        .info-box  { background: #eff6ff; padding: 12px 15px; border-radius: 6px; margin-bottom: 18px; }
        .info-grid { display: table; width: 100%; }
        .info-row  { display: table-row; }
        .info-cell { display: table-cell; width: 50%; padding: 3px 0; }
        .label     { color: #6b7280; font-size: 10px; text-transform: uppercase; letter-spacing: .05em; }
        .value     { font-weight: bold; color: #111; }
        h3         { font-size: 13px; color: #1e40af; border-bottom: 1px solid #bfdbfe; padding-bottom: 5px; margin: 18px 0 10px; }
        table      { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th         { background: #dbeafe; color: #1e40af; font-size: 10px; padding: 6px 8px; text-align: left; }
        td         { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 11px; }
        tr:nth-child(even) td { background: #f8fafc; }
        .grade-a   { color: #15803d; font-weight: bold; }
        .grade-b   { color: #1d4ed8; font-weight: bold; }
        .grade-c   { color: #92400e; font-weight: bold; }
        .grade-d   { color: #c2410c; font-weight: bold; }
        .grade-f   { color: #b91c1c; font-weight: bold; }
        .badge-green  { background: #dcfce7; color: #15803d; padding: 2px 7px; border-radius: 4px; font-size: 10px; }
        .badge-yellow { background: #fef9c3; color: #854d0e; padding: 2px 7px; border-radius: 4px; font-size: 10px; }
        .badge-red    { background: #fee2e2; color: #b91c1c; padding: 2px 7px; border-radius: 4px; font-size: 10px; }
        .footer    { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px; color: #9ca3af; font-size: 9px; text-align: center; }
    </style>
</head>
<body>

<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>Student Progress Report</h2>
    <p>Generated {{ now()->format('d F Y') }} &nbsp;|&nbsp; Guardian Copy</p>
</div>

{{-- Student Info --}}
<div class="info-box">
    <div class="info-grid">
        <div class="info-row">
            <div class="info-cell">
                <div class="label">Student Name</div>
                <div class="value">{{ $child->name }}</div>
            </div>
            <div class="info-cell">
                <div class="label">Student Number</div>
                <div class="value">{{ $child->studentProfile?->student_number ?? '—' }}</div>
            </div>
        </div>
        <div class="info-row">
            <div class="info-cell">
                <div class="label">Form Level</div>
                <div class="value">{{ $child->studentProfile?->form_level ?? '—' }}</div>
            </div>
            <div class="info-cell">
                <div class="label">Stream</div>
                <div class="value">{{ $child->studentProfile?->stream ?? '—' }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Academic Results --}}
@php
    $resultsByTerm = $child->academicResults->sortBy(['form_level','term'])->groupBy(fn($r) => ($r->form_level ? $r->form_level . ' — ' : '') . $r->term);
@endphp

@foreach($resultsByTerm as $termLabel => $results)
<h3>{{ $termLabel }}</h3>
<table>
    <thead>
        <tr>
            <th>Subject</th>
            <th>Score</th>
            <th>Grade</th>
            <th>Remarks</th>
        </tr>
    </thead>
    <tbody>
        @foreach($results->sortBy('subject.name') as $result)
        @php
            $s = $result->score;
            $gc = $s >= 80 ? 'grade-a' : ($s >= 70 ? 'grade-b' : ($s >= 60 ? 'grade-c' : ($s >= 50 ? 'grade-d' : 'grade-f')));
        @endphp
        <tr>
            <td>{{ $result->subject?->name ?? '—' }}</td>
            <td>{{ number_format($s, 1) }}%</td>
            <td class="{{ $gc }}">{{ $result->grade }}</td>
            <td style="color:#6b7280">{{ $result->teacher_remarks ?? '' }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="1" style="font-weight:bold;color:#374151">Term Average</td>
            <td style="font-weight:bold">{{ number_format($results->avg('score'), 1) }}%</td>
            <td colspan="2"></td>
        </tr>
    </tbody>
</table>
@endforeach

@if($child->academicResults->isEmpty())
<p style="color:#9ca3af;font-style:italic">No academic results recorded yet.</p>
@endif

{{-- Career Recommendations --}}
@php $careers = $child->recommendations->where('type','career')->sortByDesc('confidence_score')->take(3); @endphp
@if($careers->isNotEmpty())
<h3>Career Recommendations</h3>
<table>
    <thead><tr><th>Career Path</th><th>Category</th><th>Match Score</th></tr></thead>
    <tbody>
        @foreach($careers as $rec)
        <tr>
            <td>{{ $rec->recommended?->title ?? '—' }}</td>
            <td>{{ $rec->recommended?->category ?? '—' }}</td>
            <td>
                @php $pct = round($rec->confidence_score); @endphp
                <span class="{{ $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red') }}">
                    {{ $pct }}%
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

{{-- University Eligibility --}}
@php $unis = $child->recommendations->where('type','university_program')->sortByDesc('confidence_score')->take(5); @endphp
@if($unis->isNotEmpty())
<h3>University Programme Eligibility</h3>
<table>
    <thead><tr><th>Programme</th><th>University</th><th>Eligibility</th></tr></thead>
    <tbody>
        @foreach($unis as $rec)
        <tr>
            <td>{{ $rec->recommended?->name ?? '—' }}</td>
            <td>{{ $rec->recommended?->university ?? '—' }}</td>
            <td>
                @php $pct = round($rec->confidence_score); @endphp
                <span class="{{ $pct >= 80 ? 'badge-green' : ($pct >= 60 ? 'badge-yellow' : 'badge-red') }}">
                    {{ $pct >= 80 ? 'Eligible' : ($pct >= 60 ? 'Borderline' : 'Below threshold') }}
                </span>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    Smart Guidance Tool &nbsp;|&nbsp; Luwinga Secondary School &nbsp;|&nbsp; Confidential — for guardian use only
</div>

</body>
</html>
