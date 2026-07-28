<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Progress Report</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #0f172a;
            margin: 0;
            padding: 24px;
        }
        .header {
            text-align: center;
            margin-bottom: 24px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 16px;
        }
        .header h1 {
            color: #1d4ed8;
            margin: 0 0 4px;
            font-size: 22px;
            font-weight: bold;
        }
        .header p {
            color: #64748b;
            margin: 2px 0;
            font-size: 11px;
        }
        .meta {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }
        .meta-row { display: table-row; }
        .meta-cell {
            display: table-cell;
            padding: 6px 12px;
            background: #eff6ff;
            border: 1px solid #dbeafe;
        }
        .meta-cell strong { color: #1d4ed8; }
        table.data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table.data th, table.data td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: left;
            vertical-align: top;
        }
        table.data th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        table.data tr:nth-child(even) td { background-color: #f8fafc; }
        .badge-completed { color: #059669; font-weight: bold; }
        .badge-pending { color: #d97706; font-weight: bold; }
        .score-high { color: #059669; font-weight: bold; }
        .score-mid { color: #2563eb; font-weight: bold; }
        .score-low { color: #dc2626; }
        .footer {
            text-align: center;
            font-size: 9px;
            color: #94a3b8;
            margin-top: 24px;
            border-top: 1px solid #e2e8f0;
            padding-top: 12px;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1d4ed8;
            margin: 0 0 10px;
            padding-bottom: 4px;
            border-bottom: 1px solid #dbeafe;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Smart Career &amp; Subject Guidance Tool</h1>
        <p>Student Progress Report</p>
        <p>Generated: {{ now()->format('l, F d, Y \a\t H:i') }}</p>
    </div>

    <p class="section-title">Summary</p>
    <div class="meta">
        <div class="meta-row">
            <div class="meta-cell"><strong>Total Students:</strong> {{ $summary['total'] }}</div>
            <div class="meta-cell"><strong>Assessments Completed:</strong> {{ $summary['with_assessment'] }}</div>
            <div class="meta-cell"><strong>Avg Career Match:</strong> {{ $summary['avg_career_match'] }}%</div>
        </div>
    </div>

    <p class="section-title">Student Details</p>
    <table class="data">
        <thead>
            <tr>
                <th>#</th>
                <th>Student Name</th>
                <th>Student No.</th>
                <th>Form</th>
                <th>Stream</th>
                <th>Avg Score</th>
                <th>Assessment</th>
                <th>Top Career Match</th>
                <th>Match %</th>
            </tr>
        </thead>
        <tbody>
            @foreach($students as $index => $student)
            @php
                $hasAssessment = $student->assessmentAttempts->where('status', 'completed')->isNotEmpty();
                $topCareer = $student->recommendations
                    ->where('type', 'career')
                    ->sortByDesc('confidence_score')
                    ->first();
                $avgScore = round($student->academicResults->avg('score') ?? 0);
                $matchScore = round($topCareer?->confidence_score ?? 0);
            @endphp
            <tr>
                <td>{{ $index + 1 }}</td>
                <td><strong>{{ $student->name }}</strong></td>
                <td>{{ $student->studentProfile?->student_number ?? 'N/A' }}</td>
                <td>{{ $student->studentProfile?->form_level ?? 'N/A' }}</td>
                <td>{{ $student->studentProfile?->stream ?? 'N/A' }}</td>
                <td>{{ $avgScore }}%</td>
                <td>
                    <span class="{{ $hasAssessment ? 'badge-completed' : 'badge-pending' }}">
                        {{ $hasAssessment ? 'Completed' : 'Pending' }}
                    </span>
                </td>
                <td>{{ $topCareer?->recommended?->title ?? 'Not assessed' }}</td>
                <td class="{{ $matchScore >= 75 ? 'score-high' : ($matchScore >= 50 ? 'score-mid' : 'score-low') }}">
                    {{ $hasAssessment ? $matchScore . '%' : '—' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This report was automatically generated by the Smart Career &amp; Subject Guidance Tool.</p>
        <p>Confidential — For school administration and teaching staff only.</p>
    </div>
</body>
</html>
