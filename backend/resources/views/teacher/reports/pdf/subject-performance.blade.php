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
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #1d4ed8; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .score-high { color: #16a34a; font-weight: bold; }
  .score-mid  { color: #d97706; font-weight: bold; }
  .score-low  { color: #dc2626; font-weight: bold; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Subject Performance Report</h2>
  <p>
    @if(!empty($dateFrom)) From: {{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }} @endif
    @if(!empty($dateTo)) To: {{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }} @endif
    &bull; Generated: {{ now()->format('d M Y, H:i') }}
    @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif
  </p>
</div>

@php $subjects = $subjectData ?? $data ?? []; @endphp

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Subject</th>
      <th>Students</th>
      <th>Avg Score</th>
      <th>Highest</th>
      <th>Lowest</th>
    </tr>
  </thead>
  <tbody>
    @forelse($subjects as $i => $row)
    @php
      $row   = is_array($row) ? $row : (array)$row;
      $avg   = $row['avg_score'] ?? 0;
      $cls   = $avg >= 70 ? 'score-high' : ($avg >= 50 ? 'score-mid' : 'score-low');
    @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td><strong>{{ $row['name'] ?? '—' }}</strong></td>
      <td>{{ $row['students_count'] ?? 0 }}</td>
      <td class="{{ $cls }}">{{ round($avg, 1) }}%</td>
      <td>{{ round($row['highest_score'] ?? 0, 1) }}%</td>
      <td>{{ round($row['lowest_score'] ?? 0, 1) }}%</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">No subject data found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
