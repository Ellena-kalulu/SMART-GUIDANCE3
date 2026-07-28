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
  .info-grid { display: table; width: 100%; margin-bottom: 14px; }
  .info-col  { display: table-cell; width: 50%; vertical-align: top; padding-right: 10px; }
  .info-row  { margin-bottom: 5px; font-size: 10px; }
  .info-lbl  { color: #888; }
  .info-val  { font-weight: bold; }
  .section-title { font-size: 11px; font-weight: bold; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; border-bottom: 1px solid #e5e7eb; padding-bottom: 3px; margin: 14px 0 8px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
  th { background: #1d4ed8; color: #fff; padding: 5px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .score-high { color: #16a34a; font-weight: bold; }
  .score-mid  { color: #d97706; font-weight: bold; }
  .score-low  { color: #dc2626; font-weight: bold; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; border-top: 1px solid #e5e7eb; padding-top: 6px; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Individual Student Report Card</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php
  $profile = $student->studentProfile;
  $academicSummary = $academicSummary ?? [];
@endphp

<div class="info-grid">
  <div class="info-col">
    <div class="info-row"><span class="info-lbl">Name: </span><span class="info-val">{{ $student->name }}</span></div>
    <div class="info-row"><span class="info-lbl">Reg. No.: </span><span class="info-val">{{ $profile->student_number ?? '—' }}</span></div>
    <div class="info-row"><span class="info-lbl">Form: </span><span class="info-val">{{ $profile->form_level ?? '—' }}</span></div>
    <div class="info-row"><span class="info-lbl">Stream: </span><span class="info-val">{{ $profile->stream ?? '—' }}</span></div>
  </div>
  <div class="info-col">
    <div class="info-row"><span class="info-lbl">Email: </span>{{ $student->email }}</div>
    <div class="info-row"><span class="info-lbl">Assessment Status: </span>
      <span class="info-val">{{ $assessments->where('status','completed')->count() > 0 ? 'Completed' : 'Pending' }}</span>
    </div>
    <div class="info-row"><span class="info-lbl">Academic Average: </span>
      <span class="info-val">{{ isset($academicSummary['average']) ? round($academicSummary['average'], 1) . '%' : '—' }}</span>
    </div>
  </div>
</div>

<div class="section-title">Academic Performance</div>
<table>
  <thead><tr><th>Subject</th><th>Score</th><th>Grade</th></tr></thead>
  <tbody>
    @forelse($student->academicResults as $result)
    @php
      $score = $result->score ?? 0;
      $grade = $result->grade ?? ($score >= 80 ? 'A' : ($score >= 70 ? 'B' : ($score >= 60 ? 'C' : ($score >= 50 ? 'D' : 'F'))));
      $cls   = $score >= 70 ? 'score-high' : ($score >= 50 ? 'score-mid' : 'score-low');
    @endphp
    <tr>
      <td>{{ $result->subject->name ?? '—' }}</td>
      <td class="{{ $cls }}">{{ round($score, 1) }}%</td>
      <td>{{ $grade }}</td>
    </tr>
    @empty
    <tr><td colspan="3" style="text-align:center;color:#aaa;">No academic results recorded.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="section-title">Career Recommendations</div>
<table>
  <thead><tr><th>#</th><th>Career</th><th>Category</th><th>Match %</th></tr></thead>
  <tbody>
    @forelse($topCareers as $i => $rec)
    @php $score = $rec->confidence_score ?? 0; $cls = $score >= 70 ? 'score-high' : ($score >= 50 ? 'score-mid' : 'score-low'); @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td><strong>{{ $rec->recommended->title ?? '—' }}</strong></td>
      <td>{{ $rec->recommended->category ?? '—' }}</td>
      <td class="{{ $cls }}">{{ round($score, 1) }}%</td>
    </tr>
    @empty
    <tr><td colspan="4" style="text-align:center;color:#aaa;">No career recommendations yet.</td></tr>
    @endforelse
  </tbody>
</table>

@if($universities->count() > 0)
<div class="section-title">Recommended University Programs</div>
<table>
  <thead><tr><th>Program</th><th>University</th><th>Match %</th></tr></thead>
  <tbody>
    @foreach($universities->take(5) as $rec)
    @php $score = $rec->confidence_score ?? 0; $cls = $score >= 70 ? 'score-high' : 'score-mid'; @endphp
    <tr>
      <td><strong>{{ $rec->recommended->title ?? '—' }}</strong></td>
      <td>{{ $rec->recommended->university->name ?? '—' }}</td>
      <td class="{{ $cls }}">{{ round($score, 1) }}%</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">
  Luwinga Secondary School &bull; Smart Career &amp; Subject Guidance System &bull; {{ now()->format('d M Y') }}
</div>
</body>
</html>
