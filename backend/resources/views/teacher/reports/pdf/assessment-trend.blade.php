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
  .bar-wrap { background: #e5e7eb; border-radius: 4px; height: 12px; width: 100%; }
  .bar-fill { background: #1d4ed8; border-radius: 4px; height: 12px; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Assessment Completion Trend</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php
  $trend  = $trendData ?? $data ?? [];
  $labels = $trend['labels'] ?? collect();
  $points = $trend['data']   ?? collect();
  $max    = $points->max() ?: 1;
@endphp

<table>
  <thead>
    <tr><th>Period</th><th>Completions</th><th style="width:40%">Trend</th></tr>
  </thead>
  <tbody>
    @foreach($labels as $idx => $label)
    @php $val = $points[$idx] ?? 0; $pct = round(($val / $max) * 100); @endphp
    <tr>
      <td><strong>{{ $label }}</strong></td>
      <td>{{ $val }}</td>
      <td>
        <div class="bar-wrap">
          <div class="bar-fill" style="width:{{ $pct }}%;"></div>
        </div>
      </td>
    </tr>
    @endforeach
  </tbody>
</table>

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
