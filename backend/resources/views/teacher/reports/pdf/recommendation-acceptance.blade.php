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
  .stat-box { display: table-cell; width: 20%; text-align: center; padding: 12px 6px; border: 1px solid #e5e7eb; }
  .stat-val { font-size: 22px; font-weight: bold; color: #1d4ed8; }
  .stat-lbl { font-size: 9px; color: #888; margin-top: 4px; }
  .stat-green .stat-val { color: #16a34a; }
  .stat-red   .stat-val { color: #dc2626; }
  .stat-amber .stat-val { color: #d97706; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #1d4ed8; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Recommendation Acceptance Report</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php
  $acc  = $acceptanceData ?? $data ?? [];
  $acc  = is_array($acc) ? $acc : (array)$acc;
  $totl = $acc['total']           ?? 0;
  $acpt = $acc['accepted']        ?? 0;
  $dism = $acc['dismissed']       ?? 0;
  $pend = $acc['pending']         ?? 0;
  $rate = $acc['acceptance_rate'] ?? ($totl > 0 ? round(($acpt / $totl) * 100, 1) : 0);
@endphp

<div class="stats">
  <div class="stat-box"><div class="stat-val">{{ $totl }}</div><div class="stat-lbl">Total</div></div>
  <div class="stat-box stat-green"><div class="stat-val">{{ $acpt }}</div><div class="stat-lbl">Accepted</div></div>
  <div class="stat-box stat-red"><div class="stat-val">{{ $dism }}</div><div class="stat-lbl">Dismissed</div></div>
  <div class="stat-box stat-amber"><div class="stat-val">{{ $pend }}</div><div class="stat-lbl">Pending</div></div>
  <div class="stat-box stat-green"><div class="stat-val">{{ $rate }}%</div><div class="stat-lbl">Acceptance Rate</div></div>
</div>

@if(!empty($acc['by_career']))
<table>
  <thead><tr><th>Career</th><th>Total Recommendations</th><th>Accepted</th><th>Dismissed</th><th>Rate</th></tr></thead>
  <tbody>
    @foreach($acc['by_career'] as $career => $row)
    @php
      $r = is_array($row) ? $row : (array)$row;
      $rt = $r['total'] ?? 0;
      $ra = $r['accepted'] ?? 0;
      $rr = $rt > 0 ? round(($ra / $rt) * 100, 1) : 0;
    @endphp
    <tr>
      <td><strong>{{ $career }}</strong></td>
      <td>{{ $rt }}</td>
      <td style="color:#16a34a;font-weight:bold;">{{ $ra }}</td>
      <td style="color:#dc2626;">{{ $r['dismissed'] ?? 0 }}</td>
      <td>{{ $rr }}%</td>
    </tr>
    @endforeach
  </tbody>
</table>
@endif

<div class="footer">{{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
