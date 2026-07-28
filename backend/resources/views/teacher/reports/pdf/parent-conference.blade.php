<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
  .header { text-align: center; border-bottom: 2px solid #1d4ed8; padding-bottom: 12px; margin-bottom: 18px; }
  .header h1 { font-size: 16px; color: #1d4ed8; margin: 0 0 2px; }
  .header h2 { font-size: 13px; margin: 8px 0 4px; }
  .header p  { font-size: 9px; color: #666; margin: 2px 0; }
  .student-banner { background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 4px; padding: 10px 14px; margin-bottom: 14px; font-size: 10px; }
  .student-banner strong { font-size: 13px; display: block; margin-bottom: 4px; }
  .section-title { font-size: 11px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin: 14px 0 8px; }
  .highlight-title { font-size: 11px; font-weight: bold; color: #15803d; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #bbf7d0; padding-bottom: 3px; margin: 14px 0 8px; }
  .concern-title   { font-size: 11px; font-weight: bold; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #fecaca; padding-bottom: 3px; margin: 14px 0 8px; }
  ul { margin: 0 0 8px 16px; padding: 0; }
  ul li { margin-bottom: 4px; font-size: 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  th { background: #1d4ed8; color: #fff; padding: 5px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 6px; }
  .sig-area { display: table; width: 100%; margin-top: 30px; }
  .sig-col  { display: table-cell; width: 33%; text-align: center; padding: 0 10px; }
  .sig-line { border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; font-size: 9px; color: #555; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Parent-Teacher Conference Report</h2>
  <p>Date: {{ now()->format('d M Y') }} @if(!empty($generatedBy)) &bull; Prepared By: {{ $generatedBy }} @endif</p>
</div>

@php $profile = $student->studentProfile; @endphp
<div class="student-banner">
  <strong>{{ $student->name }}</strong>
  Form: {{ $profile->form_level ?? '—' }} &nbsp;&bull;&nbsp; Stream: {{ $profile->stream ?? '—' }} &nbsp;&bull;&nbsp; Reg. No.: {{ $profile->student_number ?? '—' }}
</div>

@if(!empty($highlights))
<div class="highlight-title">Highlights &amp; Strengths</div>
<ul>
  @foreach((array)$highlights as $highlight)
  <li>{{ is_array($highlight) ? ($highlight['text'] ?? $highlight['title'] ?? json_encode($highlight)) : $highlight }}</li>
  @endforeach
</ul>
@endif

@if(!empty($concerns))
<div class="concern-title">Areas of Concern</div>
<ul>
  @foreach((array)$concerns as $concern)
  <li>{{ is_array($concern) ? ($concern['text'] ?? $concern['title'] ?? json_encode($concern)) : $concern }}</li>
  @endforeach
</ul>
@endif

@if(!empty($recommendations))
<div class="section-title">Recommendations for Parents</div>
<ul>
  @foreach((array)$recommendations as $rec)
  <li>{{ is_array($rec) ? ($rec['text'] ?? $rec['title'] ?? json_encode($rec)) : $rec }}</li>
  @endforeach
</ul>
@endif

@if($student->academicResults->count() > 0)
<div class="section-title">Academic Summary</div>
<table>
  <thead><tr><th>Subject</th><th>Score</th><th>Grade</th></tr></thead>
  <tbody>
    @foreach($student->academicResults as $result)
    @php
      $score = $result->score ?? 0;
      $grade = $result->grade ?? ($score >= 80 ? 'A' : ($score >= 70 ? 'B' : ($score >= 60 ? 'C' : ($score >= 50 ? 'D' : 'F'))));
    @endphp
    <tr>
      <td>{{ $result->subject->name ?? '—' }}</td>
      <td>{{ round($score, 1) }}%</td>
      <td>{{ $grade }}</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="sig-area">
  <div class="sig-col"><div class="sig-line">Class Teacher</div></div>
  <div class="sig-col"><div class="sig-line">School Counsellor</div></div>
  <div class="sig-col"><div class="sig-line">Parent / Guardian</div></div>
</div>

<div class="footer">
  Luwinga Secondary School &bull; Smart Career &amp; Subject Guidance System &bull; {{ now()->format('d M Y') }}
</div>
</body>
</html>
