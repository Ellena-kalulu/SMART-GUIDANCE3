<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Saved Recommendations — {{ $user->name }}</title>
<style>
body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; line-height: 1.6; color: #000000; margin: 0; padding: 20px; }
.header { text-align: center; margin-bottom: 24px; border-bottom: 3px solid #2563eb; padding-bottom: 16px; }
.header h1 { color: #1e40af; margin: 0; font-size: 22px; font-weight: bold; }
.header h2 { color: #2563eb; margin: 6px 0 0; font-size: 16px; }
.header p { margin: 4px 0 0; color: #000000; font-size: 11px; }
.student-info { background: #eff6ff; padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; }
.info-row { display: inline-block; width: 48%; margin-bottom: 4px; }
.label { font-weight: bold; color: #374151; display: inline-block; width: 110px; }
.value { color: #1e40af; }
.section-title { color: #1e40af; font-size: 14px; font-weight: bold; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 2px solid #2563eb; }
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th { background-color: #2563eb; color: #ffffff; padding: 7px 10px; text-align: left; font-size: 11px; }
td { border: 1px solid #dbeafe; padding: 8px 10px; font-size: 11px; vertical-align: top; }
tr:nth-child(even) td { background-color: #eff6ff; }
.type-career { background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
.type-subject { background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
.type-university { background: #dbeafe; color: #000000; padding: 2px 8px; border-radius: 10px; font-size: 10px; font-weight: bold; }
.score-col { color: #2563eb; font-weight: bold; }
.saved-badge { background: #eff6ff; border-left: 4px solid #2563eb; padding: 8px 14px; border-radius: 4px; margin-bottom: 14px; font-size: 11px; }
.footer { text-align: center; font-size: 10px; color: #000000; margin-top: 30px; border-top: 1px solid #dbeafe; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>Saved Recommendations Report</h2>
    <p>Smart Career &amp; Subject Guidance Tool &mdash; Generated {{ now()->format('F d, Y') }}</p>
</div>

<div class="student-info">
    <div class="info-row"><span class="label">Student:</span><span class="value">{{ $user->name }}</span></div>
    <div class="info-row"><span class="label">Email:</span><span class="value">{{ $user->email }}</span></div>
    <div class="info-row"><span class="label">Form Level:</span><span class="value">{{ $user->studentProfile?->form_level ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Total Saved:</span><span class="value">{{ $saved->count() }} recommendation(s)</span></div>
    <div class="info-row"><span class="label">Report Date:</span><span class="value">{{ now()->format('d M Y') }}</span></div>
</div>

@if($saved->isEmpty())
<div style="text-align:center; padding:30px; color:#000000;">
    No saved recommendations yet. Visit the Recommendations page and click "Save" on careers and programmes you like.
</div>
@else

<div class="saved-badge">
    You have saved <strong>{{ $saved->count() }}</strong> recommendation(s). These represent your preferred career paths, subject choices, and university programmes.
</div>

<div class="section-title">All Saved Items</div>

@php
    $byType = $saved->groupBy('type');
    $typeLabels = [
        'career'               => 'Career',
        'subject_combination'  => 'Subject Combination',
        'university_program'   => 'University Programme',
    ];
@endphp

<table>
    <thead>
        <tr>
            <th style="width:8%">#</th>
            <th style="width:35%">Name / Title</th>
            <th style="width:18%">Type</th>
            <th style="width:12%">Match Score</th>
            <th style="width:27%">Reason</th>
        </tr>
    </thead>
    <tbody>
    @foreach($saved as $i => $rec)
    @php
        $item  = $rec->recommended;
        $title = $item?->title ?? $item?->name ?? 'N/A';
        $typeClass = $rec->type === 'career' ? 'type-career'
                   : ($rec->type === 'subject_combination' ? 'type-subject' : 'type-university');
    @endphp
    <tr>
        <td style="text-align:center;">{{ $i + 1 }}</td>
        <td><strong>{{ $title }}</strong>
            @if($rec->type === 'university_program' && $item?->university)
            <br><span style="font-size:10px; color:#000000;">{{ $item->university }}</span>
            @endif
            @if($rec->type === 'career' && $item?->category)
            <br><span style="font-size:10px; color:#000000;">{{ $item->category }}</span>
            @endif
        </td>
        <td><span class="{{ $typeClass }}">{{ $typeLabels[$rec->type] ?? $rec->type }}</span></td>
        <td class="score-col">{{ number_format($rec->confidence_score) }}%</td>
        <td style="color:#000000;">{{ \Illuminate\Support\Str::limit($rec->reason, 80) }}</td>
    </tr>
    @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    <p>This report is auto-generated by the Smart Career &amp; Subject Guidance Tool.</p>
    <p>Luwinga Secondary School, Mzuzu City, Malawi &mdash; Discuss your saved goals with your school counsellor.</p>
</div>
</body>
</html>
