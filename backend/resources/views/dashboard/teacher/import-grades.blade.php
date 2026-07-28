@extends('layouts.dashboard')

@section('title', 'Import Student Grades')

@section('sidebar')
    @include('dashboard.teacher.partials.sidebar')
@endsection

@section('breadcrumb')
<nav class="flex items-center gap-2 text-sm">
    <a href="{{ route('teacher.dashboard') }}" class="text-black/50 hover:text-blue-600">Dashboard</a>
    <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
    </svg>
    <span class="text-black/80 font-medium">Import Grades</span>
</nav>
@endsection

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black text-black">Import Student Grades</h1>
        <p class="text-black/50 text-sm mt-1">Upload an Excel or CSV file to bulk-import academic results.</p>
    </div>

    {{-- Success / error feedback --}}
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 rounded-2xl p-4">
        <p class="text-green-800 text-sm font-semibold">{{ session('success') }}</p>

        @if(session('import_skipped') && count(session('import_skipped')))
        <div class="mt-3">
            <p class="text-xs font-semibold text-yellow-700 mb-1">Skipped rows:</p>
            <ul class="text-xs text-yellow-700 space-y-0.5 list-disc list-inside">
                @foreach(session('import_skipped') as $msg)
                <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(session('import_imported') && count(session('import_imported')))
        <details class="mt-3">
            <summary class="text-xs font-semibold text-green-700 cursor-pointer">
                Show {{ count(session('import_imported')) }} imported records
            </summary>
            <ul class="text-xs text-green-700 mt-1 space-y-0.5 list-disc list-inside max-h-40 overflow-y-auto">
                @foreach(session('import_imported') as $msg)
                <li>{{ $msg }}</li>
                @endforeach
            </ul>
        </details>
        @endif
    </div>
    @endif

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4">
        <ul class="text-sm text-red-700 space-y-1 list-disc list-inside">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    {{-- Upload form --}}
    <div class="bg-white rounded-2xl border border-black/15 p-6 space-y-6">

        <form action="{{ route('teacher.import.grades.store') }}" method="POST" enctype="multipart/form-data"
              class="space-y-5">
            @csrf

            {{-- Term & Form --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-black/70 mb-1.5">Term <span class="text-red-500">*</span></label>
                    <select name="term" required
                            class="w-full border border-black/15 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                        <option value="">Select term…</option>
                        @foreach($terms as $t)
                        <option value="{{ $t }}" {{ old('term') === $t ? 'selected' : '' }}>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-black/70 mb-1.5">Form Level <span class="text-red-500">*</span></label>
                    <select name="form_level" required
                            class="w-full border border-black/15 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-300 bg-white">
                        <option value="">Select form…</option>
                        @foreach($forms as $f)
                        <option value="{{ $f }}" {{ old('form_level') === $f ? 'selected' : '' }}>{{ $f }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- File upload --}}
            <div x-data="{ name: '' }">
                <label class="block text-sm font-semibold text-black/70 mb-1.5">
                    Excel / CSV File <span class="text-red-500">*</span>
                </label>
                <label class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-black/20
                              rounded-2xl cursor-pointer hover:border-blue-400 hover:bg-blue-50/30 transition-colors"
                       :class="name ? 'border-blue-400 bg-blue-50/30' : ''">
                    <div class="flex flex-col items-center gap-2 pointer-events-none" x-show="!name">
                        <svg class="w-8 h-8 text-black/30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        <p class="text-sm text-black/50">Click to upload or drag and drop</p>
                        <p class="text-xs text-black/30">.xlsx, .xls, .csv — max 5 MB</p>
                    </div>
                    <div class="flex items-center gap-2 pointer-events-none" x-show="name" x-cloak>
                        <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm font-semibold text-blue-700" x-text="name"></span>
                    </div>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" class="sr-only" required
                           @change="name = $event.target.files[0]?.name ?? ''">
                </label>
            </div>

            <button type="submit"
                    class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl transition-colors text-sm">
                Upload & Import Grades
            </button>
        </form>
    </div>

    {{-- Template & instructions --}}
    <div class="bg-white rounded-2xl border border-black/15 p-6 space-y-4">
        <h2 class="font-bold text-black/80">File Format Requirements</h2>

        <p class="text-sm text-black/60">
            Your spreadsheet must have a <strong>header row</strong> with the following columns
            (column names are case-insensitive):
        </p>

        <div class="overflow-x-auto">
            <table class="w-full text-sm border-collapse">
                <thead>
                    <tr class="bg-black/[0.03]">
                        <th class="px-4 py-2.5 text-left font-semibold text-black/60 border border-black/10 rounded-tl-lg">Column</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-black/60 border border-black/10">Required?</th>
                        <th class="px-4 py-2.5 text-left font-semibold text-black/60 border border-black/10 rounded-tr-lg">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black/5">
                    @foreach([
                        ['student_number', 'Yes', 'Must match the student\'s registered student number (e.g. S2024001)'],
                        ['subject', 'Yes', 'Exact subject name as listed in the system (e.g. Mathematics, Biology)'],
                        ['score', 'Yes', 'Percentage score 0–100 (e.g. 78 or 78.5)'],
                        ['grade', 'No', 'Letter grade A–F. Auto-derived from score if omitted.'],
                        ['remarks', 'No', 'Optional teacher comments for this result'],
                    ] as [$col, $req, $note])
                    <tr class="hover:bg-black/[0.01]">
                        <td class="px-4 py-2.5 border border-black/5 font-mono text-xs text-blue-700 font-semibold">{{ $col }}</td>
                        <td class="px-4 py-2.5 border border-black/5">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $req === 'Yes' ? 'bg-red-50 text-red-600' : 'bg-black/5 text-black/50' }}">
                                {{ $req }}
                            </span>
                        </td>
                        <td class="px-4 py-2.5 border border-black/5 text-black/50 text-xs">{{ $note }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs font-semibold text-blue-700 mb-2">Example spreadsheet layout:</p>
            <div class="overflow-x-auto">
                <table class="text-xs font-mono border-collapse">
                    <thead>
                        <tr>
                            @foreach(['student_number','subject','score','grade','remarks'] as $h)
                            <th class="px-3 py-1.5 bg-blue-100 text-blue-700 border border-blue-200 text-left">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([
                            ['S2024001','Mathematics','85','A','Excellent performance'],
                            ['S2024001','Biology','72','B','Good understanding'],
                            ['S2024002','Mathematics','55','D','Needs improvement'],
                            ['S2024002','English','68','C',''],
                        ] as $row)
                        <tr>
                            @foreach($row as $cell)
                            <td class="px-3 py-1.5 bg-white border border-blue-100 text-black/70">{{ $cell }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-xs text-black/40">
            Existing results for the same student + subject + term + form are updated, not duplicated.
            Rows with unknown student numbers or subject names are skipped and listed in the feedback.
        </p>
    </div>

</div>
@endsection
