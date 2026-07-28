<?php

namespace App\Services;

use App\Models\{AcademicResult, Subject, User};
use Symfony\Component\HttpFoundation\StreamedResponse;

class GradesCsvService
{
    public const HEADERS = ['student_number', 'student_name', 'subject', 'score', 'grade', 'remarks'];

    public function downloadTemplate(): StreamedResponse
    {
        $students = User::where('role', 'student')->with('studentProfile')->orderBy('name')->take(3)->get();
        $subjects = Subject::orderBy('name')->take(4)->pluck('name');

        return $this->streamCsv('grades-import-template.csv', function ($handle) use ($students, $subjects) {
            fputcsv($handle, self::HEADERS);
            foreach ($students as $student) {
                $no = $student->studentProfile?->student_number ?? 'STU001';
                foreach ($subjects as $subject) {
                    fputcsv($handle, [$no, $student->name, $subject, '', '', '']);
                }
            }
        });
    }

    public function exportAllGrades(): StreamedResponse
    {
        $results = AcademicResult::with(['student.studentProfile', 'subject'])
            ->orderBy('form_level')
            ->orderBy('term')
            ->orderBy('student_id')
            ->get();

        return $this->streamCsv('all-student-grades-' . now()->format('Y-m-d') . '.csv', function ($handle) use ($results) {
            fputcsv($handle, array_merge(self::HEADERS, ['form_level', 'term']));
            foreach ($results as $row) {
                fputcsv($handle, [
                    $row->student?->studentProfile?->student_number ?? '',
                    $row->student?->name ?? '',
                    $row->subject?->name ?? '',
                    $row->score,
                    $row->grade,
                    $row->teacher_remarks ?? '',
                    $row->form_level,
                    $row->term,
                ]);
            }
        });
    }

    /**
     * A ready-to-fill template for a specific form: every enrolled student
     * crossed with every subject, score left blank. Guarantees the exact
     * headers/column order the importer expects, so a teacher filling this
     * in and re-uploading it can't hit a header-mismatch import error.
     */
    public function downloadBlankFormTemplate(string $formLevel): StreamedResponse
    {
        $students = User::where('role', 'student')
            ->whereHas('studentProfile', fn ($q) => $q->where('form_level', $formLevel))
            ->with('studentProfile')
            ->orderBy('name')
            ->get();

        $subjects = Subject::orderBy('name')->pluck('name');

        $slug = str_replace(' ', '-', strtolower($formLevel));

        return $this->streamCsv("grades-template-{$slug}.csv", function ($handle) use ($students, $subjects) {
            fputcsv($handle, self::HEADERS);
            foreach ($students as $student) {
                $no = $student->studentProfile?->student_number ?? '';
                foreach ($subjects as $subject) {
                    fputcsv($handle, [$no, $student->name, $subject, '', '', '']);
                }
            }
        });
    }

    public function exportFormCsv(string $formLevel, string $term = 'Term 3'): StreamedResponse
    {
        $results = AcademicResult::with(['student.studentProfile', 'subject'])
            ->where('form_level', $formLevel)
            ->where('term', $term)
            ->orderBy('student_id')
            ->get();

        $slug = str_replace(' ', '-', strtolower($formLevel));

        return $this->streamCsv("grades-{$slug}-{$term}.csv", function ($handle) use ($results) {
            fputcsv($handle, self::HEADERS);
            foreach ($results as $row) {
                fputcsv($handle, [
                    $row->student?->studentProfile?->student_number ?? '',
                    $row->student?->name ?? '',
                    $row->subject?->name ?? '',
                    $row->score,
                    $row->grade,
                    $row->teacher_remarks ?? '',
                ]);
            }
        });
    }

    public function writeSampleFilesToDisk(string $directory): array
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $written = [];
        foreach (['Form 1', 'Form 2', 'Form 3', 'Form 4'] as $form) {
            foreach (['Term 1', 'Term 2', 'Term 3'] as $term) {
                $results = AcademicResult::with(['student.studentProfile', 'subject'])
                    ->where('form_level', $form)
                    ->where('term', $term)
                    ->get();

                if ($results->isEmpty()) {
                    continue;
                }

                $filename = str_replace(' ', '-', strtolower($form)) . '-' . str_replace(' ', '-', strtolower($term)) . '.csv';
                $path = rtrim($directory, '/\\') . DIRECTORY_SEPARATOR . $filename;
                $handle = fopen($path, 'w');
                fputcsv($handle, self::HEADERS);
                foreach ($results as $row) {
                    fputcsv($handle, [
                        $row->student?->studentProfile?->student_number ?? '',
                        $row->student?->name ?? '',
                        $row->subject?->name ?? '',
                        $row->score,
                        $row->grade,
                        $row->teacher_remarks ?? '',
                    ]);
                }
                fclose($handle);
                $written[] = $path;
            }
        }

        return $written;
    }

    private function streamCsv(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer) {
            $handle = fopen('php://output', 'w');
            $writer($handle);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}