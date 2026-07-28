<?php
$layout = file_get_contents('c:/laragon/www/SMART-GUIDANCE-TOOL-main/resources/views/layouts/dashboard.blade.php');

$layout = str_replace(
    "<div x-data=\"{ mobileOpen: false, sidebarCollapsed: localStorage.getItem('cg-sidebar-collapsed') === '1' }\"",
    "<div x-data=\"{ mobileOpen: false, sidebarCollapsed: {{ auth()->user()->isStudent() ? 'false' : \"localStorage.getItem('cg-sidebar-collapsed') === '1'\" }} }\"",
    $layout
);

$layout = str_replace(
    "           :class=\"{ 'translate-x-0': mobileOpen, 'collapsed': sidebarCollapsed && !mobileOpen }\">",
    "           @if(auth()->user()->isStudent())
           :class=\"{ 'translate-x-0': mobileOpen }\"
           @else
           :class=\"{ 'translate-x-0': mobileOpen, 'collapsed': sidebarCollapsed && !mobileOpen }\"
           @endif>",
    $layout
);

$layout = str_replace(
    "                    <button @click=\"sidebarCollapsed = !sidebarCollapsed\"
                            class=\"hidden lg:flex p-2 rounded-lg hover:bg-slate-100 transition-colors text-slate-500\"
                            title=\"Toggle sidebar\">
                        <svg class=\"w-5 h-5\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 6h16M4 12h16M4 18h16\"/>
                        </svg>
                    </button>
                    <div class=\"hidden sm:block shrink-0\">
                        @include('partials.language-selector')
                    </div>",
    "                    @unless(auth()->user()->isStudent())
                    <button @click=\"sidebarCollapsed = !sidebarCollapsed\"
                            class=\"hidden lg:flex p-2 rounded-lg hover:bg-slate-100 transition-colors text-slate-500\"
                            title=\"Toggle sidebar\">
                        <svg class=\"w-5 h-5\" fill=\"none\" viewBox=\"0 0 24 24\" stroke=\"currentColor\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M4 6h16M4 12h16M4 18h16\"/>
                        </svg>
                    </button>
                    @endunless
                    @unless(auth()->user()->isStudent())
                    <div class=\"hidden sm:block shrink-0\">
                        @include('partials.language-selector')
                    </div>
                    @endunless",
    $layout
);

file_put_contents('c:/laragon/www/SMART-GUIDANCE-TOOL-main/resources/views/layouts/dashboard.blade.php', $layout);
echo "layout updated\n";
