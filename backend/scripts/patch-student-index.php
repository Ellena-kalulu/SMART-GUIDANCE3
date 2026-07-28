<?php
$index = file_get_contents('c:/laragon/www/SMART-GUIDANCE-TOOL-main/resources/views/dashboard/student/index.blade.php');
$old = <<<'HTML'
@section('breadcrumb')
    <nav class="flex items-center gap-2 text-sm">
        <span class="text-black/50">Dashboard</span>
        <svg class="w-4 h-4 text-black/40" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
        <span class="text-black/80 font-medium">Student Portal</span>
    </nav>
@endsection
HTML;
$new = <<<'HTML'
@section('breadcrumb')
    <span class="text-sm text-black/80 font-medium">Student Portal</span>
@endsection
HTML;
$index = str_replace($old, $new, $index);
file_put_contents('c:/laragon/www/SMART-GUIDANCE-TOOL-main/resources/views/dashboard/student/index.blade.php', $index);
echo "index breadcrumb updated\n";
