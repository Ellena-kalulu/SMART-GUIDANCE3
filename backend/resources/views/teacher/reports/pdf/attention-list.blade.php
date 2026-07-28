<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
  .header { text-align: center; border-bottom: 2px solid #dc2626; padding-bottom: 12px; margin-bottom: 18px; }
  .header h1 { font-size: 16px; color: #1d4ed8; margin: 0 0 2px; }
  .header h2 { font-size: 13px; color: #dc2626; margin: 8px 0 4px; }
  .header p  { font-size: 9px; color: #666; margin: 2px 0; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #dc2626; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #fecaca; font-size: 10px; }
  tr:nth-child(even) td { background: #fff5f5; }
  .no-assessment { color: #dc2626; font-weight: bold; }
  .low-score { color: #d97706; font-weight: bold; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Students Needing Attention</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

<table>
  <thead>
    <tr><th>#</th><th>Student Name</th><th>Form</th><th>Stream</th><th>Assessment</th><th>Match Score</th><th>Reason for Flag</th></tr>
  </thead>
  <tbody>
    @php $list = $students ?? $data['students'] ?? [] @endphp
    @forelse($list as $i => $s)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $s['name'] }}</td>
      <td>{{ $s['form'] }}</td>
      <td>{{ $s['stream'] }}</td>
      <td class="{{ $s['has_assessment'] ? '' : 'no-assessment' }}">{{ $s['has_assessment'] ? 'Done' : 'Pending' }}</td>
      <td class="{{ $s['match_score'] < 50 ? 'low-score' : '' }}">{{ $s['match_score'] }}%</td>
      <td>{{ $s['reason'] }}</td>
    </tr>
    @empty
    <tr><td colspan="7" style="text-align:center;color:#aaa;padding:20px;">No students flagged.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Total flagged: {{ count($list ?? []) }} &bull; {{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
