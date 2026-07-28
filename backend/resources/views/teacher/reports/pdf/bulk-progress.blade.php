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
  th { background: #1d4ed8; color: #fff; padding: 6px 8px; text-align: left; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
  td { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; font-size: 10px; }
  tr:nth-child(even) td { background: #f8fafc; }
  .badge-done { color: #1d4ed8; font-weight: bold; }
  .badge-pend { color: #d97706; font-weight: bold; }
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
  <h2>Bulk Student Progress Report</h2>
  <p>
    @if(!empty($formLevel)) {{ $formLevel }} @else All Forms @endif
    @if(!empty($stream)) &bull; {{ $stream }} @endif
    &bull; Generated: {{ now()->format('d M Y, H:i') }}
    @if(!empty($generatedBy)) &bull; By: {{ $generatedBy }} @endif
  </p>
</div>

<table>
  <thead>
    <tr>
      <th>#</th><th>Student Name</th><th>Reg. No.</th><th>Form</th><th>Stream</th>
      <th>Assessment</th><th>Top Career</th><th>Match %</th><th>Academic Avg</th>
    </tr>
  </thead>
  <tbody>
    @forelse($progressData ?? [] as $i => $row)
    <tr>
      <td>{{ $i + 1 }}</td>
      <td>{{ $row['name'] }}</td>
      <td>{{ $row['student_number'] ?? 'N/A' }}</td>
      <td>{{ $row['form'] }}</td>
      <td>{{ $row['stream'] }}</td>
      <td class="{{ $row['assessment_status'] === 'Completed' ? 'badge-done' : 'badge-pend' }}">
        {{ $row['assessment_status'] }}
      </td>
      <td>{{ $row['top_career'] }}</td>
      <td class="{{ $row['match_score'] >= 70 ? 'score-high' : ($row['match_score'] >= 50 ? 'score-mid' : 'score-low') }}">
        {{ $row['match_score'] }}%
      </td>
      <td>{{ $row['academic_average'] }}</td>
    </tr>
    @empty
    <tr><td colspan="9" style="text-align:center;color:#aaa;padding:20px;">No data found.</td></tr>
    @endforelse
  </tbody>
</table>

<div class="footer">Total students: {{ count($progressData ?? []) }} &bull; Report generated {{ now()->format('d M Y \a\t H:i') }}</div>
</body>
</html>
