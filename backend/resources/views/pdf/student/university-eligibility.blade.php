<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>University Eligibility Report — {{ $user->name }}</title>
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
table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
th { background-color: #2563eb; color: #ffffff; padding: 8px 10px; text-align: left; font-size: 11px; }
td { border: 1px solid #dbeafe; padding: 7px 10px; font-size: 11px; vertical-align: top; }
tr:nth-child(even) td { background-color: #eff6ff; }
.badge-eligible { background: #dbeafe; color: #1d4ed8; padding: 2px 8px; border-radius: 10px; font-weight: bold; font-size: 10px; }
.badge-check { background: #dbeafe; color: #000000; padding: 2px 8px; border-radius: 10px; font-size: 10px; }
.score-high { color: #2563eb; font-weight: bold; }
.score-med { color: #2563eb; font-weight: bold; }
.score-low { color: #000000; font-weight: bold; }
.info-box { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 10px 14px; border-radius: 4px; margin-bottom: 16px; font-size: 11px; }
.footer { text-align: center; font-size: 10px; color: #000000; margin-top: 30px; border-top: 1px solid #dbeafe; padding-top: 10px; }
</style>
</head>
<body>
<div class="header">
    <h1>Luwinga Secondary School</h1>
    <h2>University Eligibility Report</h2>
    <p>Smart Career &amp; Subject Guidance Tool &mdash; Generated {{ now()->format('F d, Y') }}</p>
</div>

<div class="student-info">
    <div class="info-row"><span class="label">Student:</span><span class="value">{{ $user->name }}</span></div>
    <div class="info-row"><span class="label">Email:</span><span class="value">{{ $user->email }}</span></div>
    <div class="info-row"><span class="label">Form Level:</span><span class="value">{{ $user->studentProfile?->form_level ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Stream:</span><span class="value">{{ $user->studentProfile?->stream ?? 'N/A' }}</span></div>
    <div class="info-row"><span class="label">Programmes Found:</span><span class="value">{{ $universities->count() }} programme(s)</span></div>
    <div class="info-row"><span class="label">Report Date:</span><span class="value">{{ now()->format('d M Y') }}</span></div>
</div>

<div class="info-box">
    Eligibility is based on your academic results and required subjects for each programme. "Eligible" means all required subjects are in your record. "Check Requirements" means some required subjects may still be needed.
</div>

<div class="section-title">University Programme Eligibility</div>

@if($universities->isEmpty())
<p style="color:#000000; text-align:center; padding:20px;">No university programme recommendations yet. Complete the career assessment and ensure your academic results are recorded.</p>
@else
<table>
    <thead>
        <tr>
            <th style="width:30%">Programme</th>
            <th style="width:22%">University</th>
            <th style="width:16%">Faculty</th>
            <th style="width:10%">Match</th>
            <th style="width:12%">Min. Points</th>
            <th style="width:10%">Eligibility</th>
        </tr>
    </thead>
    <tbody>
    @foreach($universities as $rec)
    @php
        $prog     = $rec->recommended;
        $score    = (int) $rec->confidence_score;
        $eligible = $prog?->isStudentEligible($user);
        $cls      = $score >= 75 ? 'score-high' : ($score >= 50 ? 'score-med' : 'score-low');
    @endphp
    @if($prog)
    <tr>
        <td><strong>{{ $prog->name }}</strong>
            @if($prog->entry_requirements)
            <br><span style="font-size:10px; color:#000000;">{{ \Illuminate\Support\Str::limit($prog->entry_requirements, 80) }}</span>
            @endif
        </td>
        <td>{{ $prog->university }}</td>
        <td>{{ $prog->faculty }}</td>
        <td class="{{ $cls }}">{{ $score }}%</td>
        <td style="text-align:center;">{{ $prog->minimum_points ?? '—' }}</td>
        <td style="text-align:center;">
            @if($eligible)
            <span class="badge-eligible">Eligible</span>
            @else
            <span class="badge-check">Check Req.</span>
            @endif
        </td>
    </tr>
    @endif
    @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    <p>This report is auto-generated by the Smart Career &amp; Subject Guidance Tool.</p>
    <p>Entry requirements are indicative — always confirm directly with the university. Luwinga Secondary School, Mzuzu City, Malawi.</p>
</div>
</body>
</html>
