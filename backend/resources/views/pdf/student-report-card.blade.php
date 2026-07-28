<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Student Report Card - {{ $student->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 22px;
        }
        .header h2 {
            color: #2563eb;
            margin: 5px 0;
            font-size: 18px;
        }
        .info-section {
            background: #eff6ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }
        .info-item {
            margin-bottom: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #000000;
            display: inline-block;
            width: 120px;
        }
        .info-value {
            color: #1e40af;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000000;
            padding: 8px 10px;
            text-align: left;
        }
        th {
            background-color: #2563eb;
            color: #ffffff;
            font-weight: bold;
        }
        .section-title {
            color: #1e40af;
            font-size: 16px;
            font-weight: bold;
            margin: 20px 0 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #2563eb;
        }
        .recommendation-box {
            background: #eff6ff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
            border-left: 4px solid #2563eb;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            color: #000000;
            margin-top: 30px;
            border-top: 1px solid #000000;
            padding-top: 10px;
        }
        .grade-A { color: #2563eb; font-weight: bold; }
        .grade-B { color: #3b82f6; font-weight: bold; }
        .grade-C { color: #000000; }
        .grade-D { color: #000000; }
        .grade-F { color: #2563eb; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Luwinga Secondary School</h1>
        <h2>Student Progress Report Card</h2>
        <p>Smart Career & Subject Guidance Tool</p>
    </div>

    <div class="info-section">
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Student Name:</span>
                <span class="info-value">{{ $student->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Student Number:</span>
                <span class="info-value">{{ $student->studentProfile?->student_number ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Form Level:</span>
                <span class="info-value">{{ $student->studentProfile?->form_level ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Stream:</span>
                <span class="info-value">{{ $student->studentProfile?->stream ?? 'N/A' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Report Date:</span>
                <span class="info-value">{{ now()->format('F d, Y') }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Interests:</span>
                <span class="info-value">{{ $student->studentProfile?->interests ?? 'Not specified' }}</span>
            </div>
        </div>
    </div>

    <!-- Academic Performance -->
    <div class="section-title">📚 Academic Performance</div>
    @php
        $resultsByTerm = $student->academicResults->groupBy('term');
    @endphp

    @foreach($resultsByTerm as $term => $results)
    <table>
        <thead>
            <tr>
                <th colspan="4">{{ ucfirst($term) }} Term Results</th>
            </tr>
            <tr>
                <th>Subject</th>
                <th>Score (%)</th>
                <th>Grade</th>
                <th>Teacher Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($results as $result)
            <tr>
                <td>{{ $result->subject->name ?? 'N/A' }}</td>
                <td>{{ $result->score }}%</td>
                <td class="grade-{{ $result->grade }}">{{ $result->grade }}</td>
                <td>{{ $result->teacher_remarks ?? '—' }}</td>
            </tr>
            @endforeach
            <tr style="background-color: #000000;">
                <td><strong>Average</strong></td>
                <td colspan="3"><strong>{{ round($results->avg('score')) }}%</strong></td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <!-- Career Recommendations -->
    <div class="section-title">🎯 Career Recommendations</div>
    @php
        $careers = $student->recommendations->where('type', 'career')->sortByDesc('confidence_score');
    @endphp

    @if($careers->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Career</th>
                    <th>Match Score</th>
                    <th>Category</th>
                    <th>Reason</th>
                </tr>
            </thead>
            <tbody>
                @foreach($careers->take(5) as $rec)
                <tr>
                    <td><strong>{{ $rec->recommended->title ?? 'N/A' }}</strong></td>
                    <td>{{ $rec->confidence_score }}%</td>
                    <td>{{ $rec->recommended->category ?? 'N/A' }}</td>
                    <td>{{ Str::limit($rec->reason, 100) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No career recommendations yet. Complete the assessment to get personalized recommendations.</p>
    @endif

    <!-- University Eligibility -->
    <div class="section-title">🏛️ University Program Eligibility</div>
    @php
        $universities = $student->recommendations->where('type', 'university_program')
            ->where('confidence_score', '>=', 80)
            ->sortByDesc('confidence_score');
    @endphp

    @if($universities->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>University</th>
                    <th>Program</th>
                    <th>Eligibility Score</th>
                    <th>Requirements</th>
                </tr>
            </thead>
            <tbody>
                @foreach($universities->take(5) as $rec)
                <tr>
                    <td>{{ $rec->recommended->university ?? 'N/A' }}</td>
                    <td><strong>{{ $rec->recommended->name ?? 'N/A' }}</strong></td>
                    <td>{{ $rec->confidence_score }}%</td>
                    <td>{{ Str::limit($rec->recommended->entry_requirements ?? 'See university website', 80) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No university eligibility matches yet. Focus on your subjects and aim for higher scores.</p>
    @endif

    <!-- Recommendations Summary -->
    <div class="recommendation-box">
        <strong>📝 Guidance Summary</strong><br>
        Based on the assessment, focus on:
        <ul style="margin-top: 5px; margin-bottom: 0;">
            @foreach($careers->take(3) as $career)
            <li>{{ $career->recommended->title ?? '' }} - {{ $career->confidence_score }}% match</li>
            @endforeach
        </ul>
    </div>

    <div class="footer">
        <p>This report is auto-generated by the Smart Career & Subject Guidance Tool.</p>
        <p>For more details, log in to your account or speak with your guidance counsellor.</p>
        <p>Luwinga Secondary School, Mzuzu City, Malawi</p>
    </div>
</body>
</html>
