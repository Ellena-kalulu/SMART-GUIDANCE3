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
  <h2>Form-Level Comparison Report</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php $comparison = $comparisonData ?? $data ?? []; @endphp

<table>
  <thead>
    <tr>
      <th>Form</th>
      <th>Students</th>
      <th>Assessed</th>
      <th>Completion %</th>
      <th>Avg Match %</th>
      <th>Avg Academic %</th>
    </tr>
  </thead>
  <tbody>
    @forelse($comparison as $formName => $row)
    @php
      $row        = is_array($row) ? $row : (array)$row;
      $total      = $row['total']          ?? 0;
      $assessed   = $row['assessed']       ?? 0;
      $completion = $total > 0 ? round(($assessed / $total) * 100, 1) : 0;
      $avgMatch   = round($row['avg_match'] ?? 0, 1);
      $avgAcad    = round($row['avg_academic'] ?? 0, 1);
      $matchCls   = $avgMatch >= 70 ? 'score-high' : ($avgMatch >= 50 ? 'score-mid' : 'score-low');
      $acadCls    = $avgAcad  >= 70 ? 'score-high' : ($avgAcad  >= 50 ? 'score-mid' : 'score-low');
    @endphp
    <tr>
      <td><strong>{{ $formName }}</strong></td>
      <td>{{ $total }}</td>
      <td>{{ $assessed }}</td>
      <td>{{ $completion }}%</td>
      <td class="{{ $matchCls }}">{{ $avgMatch }}%</td>
      <td class="{{ $acadCls }}">{{ $avgAcad }}%</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">No comparison data found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
