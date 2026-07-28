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
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .high   { color: #16a34a; font-weight: bold; }
  .medium { color: #d97706; font-weight: bold; }
  .low    { color: #dc2626; font-weight: bold; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Placement Prediction Report</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php $predictions = $predictions ?? $data ?? []; @endphp

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Student</th>
      <th>Form</th>
      <th>Top Career</th>
      <th>University</th>
      <th>Match %</th>
      <th>Academic %</th>
      <th>Likelihood</th>
    </tr>
  </thead>
  <tbody>
    @forelse($predictions as $i => $row)
    @php
      $row   = is_array($row) ? $row : (array)$row;
      $match = $row['match_score'] ?? 0;
      $acad  = $row['academic_score'] ?? 0;
      $likelihood = $row['likelihood'] ?? ($match >= 70 && $acad >= 70 ? 'High' : ($match >= 50 ? 'Medium' : 'Low'));
      $cls = strtolower($likelihood);
    @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td><strong>{{ $row['name'] ?? '—' }}</strong></td>
      <td>{{ $row['form'] ?? '—' }}</td>
      <td>{{ $row['top_career'] ?? '—' }}</td>
      <td>{{ $row['university'] ?? '—' }}</td>
      <td>{{ round($match, 1) }}%</td>
      <td>{{ round($acad, 1) }}%</td>
      <td class="{{ $cls }}">{{ $likelihood }}</td>
    </tr>
    @empty
    <tr><td colspan="8" style="text-align:center;color:#aaa;padding:20px;">No prediction data found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
