<?php

namespace App\Imports;

use App\Models\{AcademicResult, Subject, User};
use Maatwebsite\Excel\Concerns\{ToCollection, WithHeadingRow, WithValidation, SkipsOnError, SkipsErrors};
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;

class AcademicResultsImport implements ToCollection, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public array $imported = [];
    public array $skipped  = [];
    /** @var array<int> Student IDs whose grades were updated */
    public array $affectedStudentIds = [];

    public function __construct(
        private readonly string $term,
        private readonly string $formLevel,
    ) {}

    public function collection(Collection $rows): void
    {
        $rowNum = 1; // row 1 is the header, so data starts at row 2
        foreach ($rows as $row) {
            $rowNum++;
            $fields = $this->resolveFields($row);

            $studentNo   = $fields['student_number'];
            $subjectName = $fields['subject'];
            $score       = $fields['score'];
            $grade       = $fields['grade'];
            $remarks     = $fields['remarks'];

            // Silently ignore fully blank rows (common trailing rows in exports)
            if ($studentNo === '' && $subjectName === '' && $score === null) {
                continue;
            }

            if ($score === null) {
                $this->skipped[] = "Row {$rowNum} ({$studentNo} / {$subjectName}): no score entered — type a score in the Score column.";
                continue;
            }

            if ($studentNo === '') {
                $this->skipped[] = "Row {$rowNum}: missing student number.";
                continue;
            }

            if ($subjectName === '') {
                $this->skipped[] = "Row {$rowNum} ({$studentNo}): missing subject.";
                continue;
            }

            $score = (float) $score;
            if ($score < 0 || $score > 100) {
                $this->skipped[] = "Skipped {$studentNo} / {$subjectName}: score {$score} out of range.";
                continue;
            }

            // Resolve student by student number
            $student = User::where('role', 'student')
                ->whereHas('studentProfile', fn($q) => $q->where('student_number', $studentNo))
                ->first();

            if (!$student) {
                $this->skipped[] = "Student number '{$studentNo}' not found — skipped.";
                continue;
            }

            // Resolve subject by name (case-insensitive)
            $subject = Subject::whereRaw('LOWER(name) = ?', [strtolower($subjectName)])->first();

            if (!$subject) {
                $this->skipped[] = "Subject '{$subjectName}' not found — skipped for {$studentNo}.";
                continue;
            }

            // Derive letter grade from score if not provided
            if ($grade === '') {
                $grade = match(true) {
                    $score >= 80 => 'A',
                    $score >= 70 => 'B',
                    $score >= 60 => 'C',
                    $score >= 50 => 'D',
                    $score >= 40 => 'E',
                    default      => 'F',
                };
            }

            AcademicResult::updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subject->id,
                    'term'       => $this->term,
                    'form_level' => $this->formLevel,
                ],
                [
                    'score'           => $score,
                    'grade'           => strtoupper($grade),
                    'teacher_remarks' => $remarks ?: null,
                ]
            );

            $this->imported[] = "{$student->name} — {$subjectName}: {$score}%";
            $this->affectedStudentIds[$student->id] = $student->id;
        }
    }

    /**
     * Pull student number / subject / score / grade / remarks out of a row,
     * tolerating header variations (spacing, casing, wording). Falls back to
     * column position (student_number, student_name, subject, score, grade,
     * remarks — matching the downloadable template) if none of the expected
     * column names are found at all, so files with unrecognised headers but
     * the template's column order still import correctly.
     */
    private function resolveFields(Collection $row): array
    {
        $normalised = [];
        foreach ($row->toArray() as $key => $value) {
            $key = strtolower(trim((string) $key));
            $key = preg_replace('/[^a-z0-9]+/', '_', $key);
            $key = trim($key, '_');
            $normalised[$key] = is_string($value) ? trim($value) : $value;
        }

        $pick = function (array $aliases) use ($normalised) {
            foreach ($aliases as $alias) {
                if (array_key_exists($alias, $normalised) && $normalised[$alias] !== '' && $normalised[$alias] !== null) {
                    return $normalised[$alias];
                }
            }
            return null;
        };

        $studentNo   = $pick(['student_number', 'student_no', 'studentnumber', 'student_id', 'admission_number', 'admission_no']);
        $subjectName = $pick(['subject', 'subject_name', 'subjectname']);
        $score       = $pick(['score', 'marks', 'mark', 'percentage', 'grade_score']);
        $grade       = $pick(['grade', 'letter_grade']);
        $remarks     = $pick(['remarks', 'teacher_remarks', 'comment', 'comments']);

        // Headers didn't match any known alias at all — fall back to column
        // position, matching GradesCsvService::HEADERS order.
        if ($studentNo === null && $subjectName === null && $score === null) {
            $values = array_values($normalised);
            $studentNo   = $values[0] ?? null;
            $subjectName = $values[2] ?? null;
            $score       = $values[3] ?? null;
            $grade       = $values[4] ?? null;
            $remarks     = $values[5] ?? null;
        }

        return [
            'student_number' => trim((string) ($studentNo ?? '')),
            'subject'        => trim((string) ($subjectName ?? '')),
            'score'          => ($score === null || $score === '') ? null : $score,
            'grade'          => trim((string) ($grade ?? '')),
            'remarks'        => trim((string) ($remarks ?? '')),
        ];
    }
}
