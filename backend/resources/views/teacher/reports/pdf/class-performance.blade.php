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
  .stats { display: table; width: 100%; margin-bottom: 18px; }
  .stat-box { display: table-cell; width: 25%; text-align: center; padding: 12px 8px; border: 1px solid #e5e7eb; }
  .stat-val { font-size: 22px; font-weight: bold; color: #1d4ed8; }
  .stat-lbl { font-size: 9px; color: #888; margin-top: 4px; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #1d4ed8; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Class Performance Overview</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php
  $perf = $performanceData ?? $data ?? [];
  $total       = $perf['total_students']    ?? 0;
  $completion  = $perf['completion_rate']   ?? 0;
  $avgMatch    = $perf['avg_match_score']   ?? 0;
  $avgAcademic = $perf['avg_academic_score']?? 0;
  $students    = $perf['students']          ?? collect();
@endphp

<div class="stats">
  <div class="stat-box"><div class="stat-val">{{ $total }}</div><div class="stat-lbl">Total Students</div></div>
  <div class="stat-box"><div class="stat-val">{{ $completion }}%</div><div class="stat-lbl">Completion Rate</div></div>
  <div class="stat-box"><div class="stat-val">{{ $avgMatch }}%</div><div class="stat-lbl">Avg Match Score</div></div>
  <div class="stat-box"><div class="stat-val">{{ $avgAcademic }}%</div><div class="stat-lbl">Avg Academic Score</div></div>
</div>

<table>
  <thead><tr><th>#</th><th>Student</th><th>Form</th><th>Assessment</th><th>Match Score</th><th>Academic Avg</th></tr></thead>
  <tbody>
    @foreach($students as $i => $student)
    @php
      $done = \App\Models\AssessmentAttempt::where('student_id', $student->id)->where('status','completed')->exists();
      $best = $student->recommendations->where('type','career')->max('confidence_score') ?? 0;
      $avg  = $student->academicResults->avg('score') ?? 0;
    @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $student->name }}</td>
      <td>{{ $student->studentProfile->form_level ?? '—' }}</td>
      <td>{{ $done ? 'Completed' : 'Pending' }}</td>
      <td>{{ round($best, 1) }}%</td>
      <td>{{ round($avg, 1) }}%</td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
