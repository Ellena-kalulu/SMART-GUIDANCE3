<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1a1a1a; margin: 0; padding: 20px; }
  .header { text-align: center; border-bottom: 2px solid #7c3aed; padding-bottom: 12px; margin-bottom: 18px; }
  .header h1 { font-size: 16px; color: #1d4ed8; margin: 0 0 2px; }
  .header h2 { font-size: 13px; color: #7c3aed; margin: 8px 0 4px; }
  .header p  { font-size: 9px; color: #666; margin: 2px 0; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th { background: #7c3aed; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; }
  td { padding: 5px 8px; border-bottom: 1px solid #ede9fe; font-size: 10px; }
  tr:nth-child(even) td { background: #faf8ff; }
  .footer { margin-top: 20px; font-size: 9px; color: #aaa; text-align: right; }
</style>
</head>
<body>
<div class="header">
  <h1>LUWINGA SECONDARY SCHOOL</h1>
  <p>Smart Career &amp; Subject Guidance System</p>
  <h2>Intervention Tracking Report</h2>
  <p>Generated: {{ now()->format('d M Y, H:i') }} @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif</p>
</div>

@php
  $interventions = $interventions ?? $data ?? collect();
  if (!($interventions instanceof \Illuminate\Support\Collection)) {
      $interventions = collect((array)$interventions);
  }
@endphp

<table>
  <thead>
    <tr>
      <th>#</th>
      <th>Date</th>
      <th>Action</th>
      <th>Subject (Student)</th>
      <th>Performed By</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>
    @forelse($interventions as $i => $log)
    @php
      $props = is_array($log) ? $log : (method_exists($log, 'toArray') ? $log->toArray() : (array)$log);
    @endphp
    <tr>
      <td>{{ $i + 1 }}</td>
      <td style="white-space:nowrap;">{{ isset($props['created_at']) ? \Carbon\Carbon::parse($props['created_at'])->format('d M Y') : '—' }}</td>
      <td><strong>{{ $props['action'] ?? $props['type'] ?? '—' }}</strong></td>
      <td>{{ $props['subject_name'] ?? $props['student_name'] ?? ($props['causer_name'] ?? '—') }}</td>
      <td>{{ $props['causer_name'] ?? $props['performed_by'] ?? '—' }}</td>
      <td style="font-size:9px;color:#555;">{{ $props['description'] ?? $props['notes'] ?? '—' }}</td>
    </tr>
    @empty
    <tr><td colspan="6" style="text-align:center;color:#aaa;padding:20px;">No intervention records found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Total records: {{ $interventions->count() }} &bull; {{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
