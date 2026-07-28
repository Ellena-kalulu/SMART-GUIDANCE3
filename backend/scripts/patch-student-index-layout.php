<?php
$base = dirname(__DIR__);
$path = $base . '/resources/views/dashboard/student/index.blade.php';
$idx = file_get_contents($path);
if (str_contains($idx, 'feature-nav')) {
    echo "already patched\n";
    exit(0);
}
$search = "@section('content')\n<div class=\"space-y-6 min-w-0\">";
$replace = "@section('content')\n<div class=\"grid grid-cols-1 lg:grid-cols-[260px_minmax(0,1fr)] gap-6 items-start\">\n\n    <aside class=\"lg:sticky lg:top-20 space-y-3 order-first\">\n        <p class=\"text-xs font-bold text-slate-500 uppercase tracking-wider px-1 hidden lg:block\">Student Features</p>\n        <div class=\"hidden lg:block\">\n            @include('dashboard.student.partials.feature-nav')\n        </div>\n        <div class=\"lg:hidden overflow-x-auto pb-1 -mx-1 px-1\">\n            <div class=\"flex gap-2 min-w-max\">\n                @foreach([\n                    ['student.dashboard', 'Dashboard'],\n                    ['student.assessment', 'Assessment'],\n                    ['student.recommendations', 'Recommendations'],\n                    ['student.subject.combinations', 'Subjects'],\n                    ['student.progress', 'Progress'],\n                    ['student.chatbot', 'AI Help'],\n                ] as [\$route, \$label])\n                <a href=\"{{ route(\$route) }}\"\n                   class=\"px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-blue-200 text-slate-700 hover:border-blue-400 hover:text-blue-700 hover:bg-blue-50 shadow-sm transition-colors\">\n                    {{ \$label }}\n                </a>\n                @endforeach\n            </div>\n        </div>\n    </aside>\n\n    <div class=\"space-y-6 min-w-0\">";
if (!str_contains($idx, $search)) {
    fwrite(STDERR, "pattern not found\n");
    exit(1);
}
$idx = str_replace($search, $replace, $idx);
$idx = preg_replace('/\n<\/div>\n@endsection\s*$/', "\n    </div>\n</div>\n@endsection\n", $idx);
file_put_contents($path, $idx);
echo "index layout patched\n";
