<?php
// Patch student index - remove Quick Access column
$f = __DIR__ . '/../resources/views/dashboard/student/index.blade.php';
$c = file_get_contents($f);
$old = <<<'OLD'
@section('content')
<div class="grid grid-cols-1 xl:grid-cols-[240px_minmax(0,1fr)] gap-6 items-start">

    <aside class="xl:sticky xl:top-20 space-y-3">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider px-1">Quick Access</p>
        <div class="hidden xl:block">
            @include('dashboard.student.partials.feature-nav')
        </div>
        <div class="xl:hidden overflow-x-auto pb-1">
            <div class="flex gap-2 min-w-max">
                @foreach([
                    ['student.assessment', 'Assessment'],
                    ['student.recommendations', 'Recommendations'],
                    ['student.subject.combinations', 'Subjects'],
                    ['student.progress', 'Progress'],
                    ['student.chatbot', 'AI Help'],
                ] as [$route, $label])
                <a href="{{ route($route) }}" class="px-3 py-2 rounded-xl text-xs font-semibold whitespace-nowrap bg-white border border-slate-200 text-slate-600 hover:border-brand-300 hover:text-brand-700">{{ $label }}</a>
                @endforeach
            </div>
        </div>
    </aside>

    <div class="space-y-6 min-w-0">
OLD;
$new = <<<'NEW'
@section('content')
<div class="space-y-6 min-w-0">
NEW;
$c = str_replace($old, $new, $c);
// remove extra closing div before @endsection if present
$c = preg_replace('/\n    <\/div>\n\n<\/div>\n@endsection/', "\n</div>\n@endsection", $c, 1);
file_put_contents($f, $c);
echo "index ok\n";

// Patch admin breadcrumb
$f2 = __DIR__ . '/../resources/views/dashboard/admin/index.blade.php';
$c2 = file_get_contents($f2);
$c2 = preg_replace(
    "/@section\('breadcrumb'\)\s*<nav class=\"flex items-center gap-2 text-sm\">.*?<\/nav>\s*@endsection/s",
    "@section('breadcrumb')\n<nav class=\"flex items-center gap-2 text-sm\">\n    <span class=\"text-slate-500\">Admin</span>\n    <svg class=\"w-4 h-4 text-slate-400\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M9 5l7 7-7 7\"/></svg>\n    <span class=\"font-semibold text-slate-700\">Dashboard</span>\n</nav>\n@endsection",
    $c2,
    1
);
file_put_contents($f2, $c2);
echo "admin ok\n";
