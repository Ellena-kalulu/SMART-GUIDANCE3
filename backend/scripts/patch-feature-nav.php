<?php
$base = dirname(__DIR__);
$path = $base . '/resources/views/dashboard/student/partials/feature-nav.blade.php';
$content = <<<'BLADE'
<nav class="bg-white rounded-2xl border border-blue-100 shadow-md p-3 space-y-1">
    @php
        $links = [
            ['route' => 'student.dashboard', 'label' => 'Dashboard', 'match' => 'student.dashboard', 'icon' => 'M4 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM14 5a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1V5zM4 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1H5a1 1 0 01-1-1v-4zM14 15a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z'],
            ['route' => 'student.assessment', 'label' => 'Self Assessment', 'match' => 'student.assessment*', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4'],
            ['route' => 'student.recommendations', 'label' => 'My Recommendations', 'match' => 'student.recommendations*', 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
            ['route' => 'student.subject.combinations', 'label' => 'Subject Combinations', 'match' => 'student.subject.combinations*', 'icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253'],
            ['route' => 'student.career.path', 'label' => 'My Career Path', 'match' => 'student.career.path*', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
            ['route' => 'student.universities', 'label' => 'University Programs', 'match' => 'student.universities*', 'icon' => 'M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z'],
            ['route' => 'student.progress', 'label' => 'Academic Progress', 'match' => 'student.progress*', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
            ['route' => 'student.appointments', 'label' => 'Appointments', 'match' => 'student.appointments*', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => 'student.chatbot', 'label' => 'AI Assistant', 'match' => 'student.chatbot*', 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ];
    @endphp
    @foreach($links as $link)
        @php $active = request()->routeIs($link['match']); @endphp
        <a href="{{ route($link['route']) }}"
           class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all {{ $active ? 'bg-gradient-to-r from-blue-600 to-blue-700 text-white shadow-md shadow-blue-600/25' : 'text-slate-600 hover:bg-blue-50 hover:text-blue-700 border border-transparent hover:border-blue-100' }}">
            <span class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0 {{ $active ? 'bg-white/20' : 'bg-blue-50 text-blue-600' }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/>
                </svg>
            </span>
            {{ $link['label'] }}
        </a>
    @endforeach
</nav>
BLADE;
file_put_contents($path, $content);
echo "feature-nav updated\n";
