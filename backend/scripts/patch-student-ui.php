<?php
$base = dirname(__DIR__);

$aiPath = $base . '/resources/views/dashboard/student/partials/academic-intelligence.blade.php';
$ai = rtrim(file_get_contents($aiPath));
if (!str_ends_with($ai, '@endif')) {
    file_put_contents($aiPath, $ai . "\n@endif\n");
}

$scPath = $base . '/resources/views/dashboard/student/subject-combinations.blade.php';
$sc = file_get_contents($scPath);
$sc = preg_replace('/Grades analysed: Form 1.+4 \(uploaded by teachers\)\. Score = subject average .+ 70% \+ interest .+ 30%\./u',
    'Grades analysed: Form 1&ndash;4 (uploaded by teachers). Score = subject average &times; 70% + interest &times; 30%.',
    $sc);
file_put_contents($scPath, $sc);

// Create feature-nav partial
$fn = <<<'BLADE'
<nav class="bg-white rounded-2xl border border-slate-200 shadow-sm p-3 space-y-1">
    @php
        $links = [
            ['route' => 'student.dashboard', 'label' => 'Dashboard', 'match' => 'student.dashboard'],
            ['route' => 'student.assessment', 'label' => 'Self Assessment', 'match' => 'student.assessment*'],
            ['route' => 'student.recommendations', 'label' => 'My Recommendations', 'match' => 'student.recommendations*'],
            ['route' => 'student.subject.combinations', 'label' => 'Subject Combinations', 'match' => 'student.subject.combinations*'],
            ['route' => 'student.career.path', 'label' => 'My Career Path', 'match' => 'student.career.path*'],
            ['route' => 'student.universities', 'label' => 'University Programs', 'match' => 'student.universities*'],
            ['route' => 'student.progress', 'label' => 'Academic Progress', 'match' => 'student.progress*'],
            ['route' => 'student.appointments', 'label' => 'Appointments', 'match' => 'student.appointments*'],
            ['route' => 'student.chatbot', 'label' => 'AI Assistant', 'match' => 'student.chatbot*'],
        ];
    @endphp
    @foreach($links as $link)
        @php $active = request()->routeIs($link['match']); @endphp
        <a href="{{ route($link['route']) }}"
           class="flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors {{ $active ? 'bg-brand-50 text-brand-700 border border-brand-100' : 'text-slate-600 hover:bg-slate-50 hover:text-brand-700' }}">
            <span class="w-1.5 h-1.5 rounded-full {{ $active ? 'bg-brand-600' : 'bg-slate-300' }}"></span>
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
BLADE;
file_put_contents($base . '/resources/views/dashboard/student/partials/feature-nav.blade.php', $fn);

// Update student index layout
$idxPath = $base . '/resources/views/dashboard/student/index.blade.php';
$idx = file_get_contents($idxPath);
if (!str_contains($idx, 'feature-nav')) {
    $idx = str_replace(
        "@section('content')\n<div class=\"space-y-6\">",
        "@section('content')\n<div class=\"grid grid-cols-1 xl:grid-cols-[240px_minmax(0,1fr)] gap-6 items-start\">\n\n    <aside class=\"xl:sticky xl:top-20 space-y-3\">\n        <p class=\"text-xs font-bold text-slate-400 uppercase tracking-wider px-1\">Quick Access</p>\n        <div class=\"hidden xl:block\">\n            @include('dashboard.student.partials.feature-nav')\n        </div>\n        <div class=\"xl:hidden overflow-x-auto pb-1\">\n            <div class=\"flex gap-2 min-w-max\">\n                @foreach([\n                    ['student.assessment', 'Assessment'],\n                    ['student.recommendations', 'Recommendations'],\n                    ['student.subject.combinations', 'Subjects'],\n                    ['student.progress', 'Progress'],\n                    ['student.chatbot', 'AI Help'],\n                ] as [\$route, \$label])\n                <a href=\"{{ route(\$route) }}\" class=\"px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700\">{{ \$label }}</a>\n                @endforeach\n            </div>\n        </div>\n    </aside>\n\n    <div class=\"space-y-6 min-w-0\">",
        $idx
    );
    $idx = preg_replace('/\n<\/div>\n@endsection\s*$/', "\n    </div>\n</div>\n@endsection\n", $idx);
    file_put_contents($idxPath, $idx);
}

echo "done\n";
