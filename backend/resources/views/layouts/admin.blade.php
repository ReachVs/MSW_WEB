<!DOCTYPE html>
<html class="light" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin') | MAD APE</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "surface-container-highest": "#e4e2e3",
                        "outline": "#75777d",
                        "secondary": "#b02d29",
                        "background": "#fbf8fa",
                        "surface-tint": "#545f73",
                        "surface-container": "#f0edef",
                        "on-surface-variant": "#45474c",
                        "surface-bright": "#fbf8fa",
                        "on-secondary": "#ffffff",
                        "inverse-surface": "#303032",
                        "on-primary-fixed-variant": "#3c475a",
                        "secondary-container": "#ff665c",
                        "surface": "#fbf8fa",
                        "primary": "#091426",
                        "primary-fixed": "#d8e3fb",
                        "on-primary-fixed": "#111c2d",
                        "surface-dim": "#dcd9db",
                        "on-primary": "#ffffff",
                        "on-error": "#ffffff",
                        "on-error-container": "#93000a",
                        "surface-container-low": "#f5f3f4",
                        "primary-fixed-dim": "#bcc7de",
                        "outline-variant": "#c5c6cd",
                        "inverse-on-surface": "#f3f0f2",
                        "error": "#ba1a1a",
                        "primary-container": "#1e293b",
                        "error-container": "#ffdad6",
                        "surface-container-lowest": "#ffffff",
                        "surface-container-high": "#eae7e9",
                        "on-primary-container": "#8590a6",
                        "on-surface": "#1b1b1d",
                        "inverse-primary": "#bcc7de",
                        "surface-variant": "#e4e2e3",
                        "on-background": "#1b1b1d",
                    },
                    borderRadius: {
                        DEFAULT: "0.25rem", lg: "0.5rem", xl: "0.75rem", full: "9999px",
                    },
                    spacing: {
                        md: "24px", gutter: "24px", unit: "4px", xl: "80px",
                        margin: "48px", lg: "48px", sm: "12px", xs: "4px",
                    },
                    fontFamily: {
                        "headline-lg": ["Space Grotesk"],
                        "headline-md": ["Space Grotesk"],
                        "body-md": ["Space Grotesk"],
                        "body-lg": ["Space Grotesk"],
                        "label-sm": ["Space Grotesk"],
                        "display-xl": ["Space Grotesk"],
                    },
                    fontSize: {
                        "headline-lg": ["40px", { lineHeight: "1.2", letterSpacing: "-0.02em", fontWeight: "600" }],
                        "headline-md": ["32px", { lineHeight: "1.3", fontWeight: "600" }],
                        "body-md": ["16px", { lineHeight: "1.6", fontWeight: "400" }],
                        "body-lg": ["18px", { lineHeight: "1.6", fontWeight: "400" }],
                        "label-sm": ["12px", { lineHeight: "1.0", letterSpacing: "0.1em", fontWeight: "700" }],
                        "display-xl": ["72px", { lineHeight: "1.1", letterSpacing: "-0.04em", fontWeight: "700" }],
                    },
                },
            },
        }
    </script>
    <style>
        body { font-family: 'Space Grotesk', sans-serif; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .grid-pattern { background-image: radial-gradient(circle, #c5c6cd 1px, transparent 1px); background-size: 24px 24px; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
    @stack('head')
</head>
<body class="bg-surface text-on-surface">
@php
    $page = $page ?? 'dashboard';
    $portalRoutePrefix = $portalRoutePrefix ?? 'admin';
    $portalTitle = $portalTitle ?? 'ADMIN';
    $showInventoryNav = $showInventoryNav ?? true;
    $showMechanicsNav = $showMechanicsNav ?? true;
    $showLogout = $showLogout ?? true;
    $compactSidebar = $compactSidebar ?? false;
    $sidebarWidthClass = $compactSidebar ? 'w-20 xl:w-56' : 'w-64';
    $headerLeftClass = $compactSidebar ? 'left-20 xl:left-56' : 'left-64';
    $mainOffsetClass = $compactSidebar ? 'ml-20 xl:ml-56' : 'ml-64';
    $mainPaddingClass = $compactSidebar ? 'p-md xl:p-lg' : 'p-lg';
    $contentMaxWidthClass = $compactSidebar ? 'max-w-none' : 'max-w-[1400px]';
    $sidebarTitleClass = $compactSidebar ? 'hidden xl:block' : '';
    $navItemClass = $compactSidebar ? 'justify-center xl:justify-start' : '';
    $navLabelClass = $compactSidebar ? 'hidden xl:inline' : '';
@endphp

{{-- SIDE NAVIGATION --}}
<aside class="fixed h-screen left-0 top-0 {{ $sidebarWidthClass }} bg-surface-container-low border-r border-outline-variant flex flex-col py-lg px-sm xl:px-md z-50">
    <div class="mb-xl {{ $sidebarTitleClass }}">
        <h1 class="font-headline-md text-headline-md text-primary tracking-tighter">WORKSHOP PROTOCOL</h1>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-primary font-bold">&nbsp;Operations APE</p>
        <p class="font-label-sm text-label-sm uppercase tracking-widest text-outline mt-xs">V2.0.4-STABLE</p>
    </div>

    @if($compactSidebar)
        <div class="mb-xl flex justify-center xl:hidden">
            <div class="rounded border border-outline-variant bg-white px-sm py-sm font-label-sm uppercase tracking-widest text-primary">
                M
            </div>
        </div>
    @endif

    <nav class="flex-1 space-y-sm">
        <a href="{{ route($portalRoutePrefix.'.dashboard') }}"
           class="flex items-center gap-md py-sm px-sm transition-all duration-200 ease-in-out {{ $navItemClass }}
               {{ $page === 'dashboard' ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container-high' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high' }}">
            <span class="material-symbols-outlined">dashboard</span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Dashboard</span>
        </a>

        @if($showInventoryNav)
            <a href="{{ route($portalRoutePrefix.'.inventory') }}"
               class="flex items-center gap-md py-sm px-sm transition-all duration-200 ease-in-out {{ $navItemClass }}
                   {{ $page === 'inventory' ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container-high' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">handyman</span>
                <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Inventory</span>
            </a>
        @endif

        <a href="{{ route($portalRoutePrefix.'.calendar') }}"
           class="flex items-center gap-md py-sm px-sm transition-all duration-200 ease-in-out {{ $navItemClass }}
               {{ $page === 'calendar' ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container-high' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high' }}">
            <span class="material-symbols-outlined">calendar_month</span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Calendar</span>
        </a>

        <a href="{{ route($portalRoutePrefix.'.queue') }}"
           class="flex items-center gap-md py-sm px-sm transition-all duration-200 ease-in-out {{ $navItemClass }}
               {{ $page === 'queue' ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container-high' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high' }}">
            <span class="material-symbols-outlined">assignment</span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Queue</span>
        </a>

        @if($showMechanicsNav)
            <a href="{{ route($portalRoutePrefix.'.mechanics') }}"
               class="flex items-center gap-md py-sm px-sm transition-all duration-200 ease-in-out {{ $navItemClass }}
                   {{ $page === 'mechanics' ? 'text-primary font-bold border-l-4 border-secondary bg-surface-container-high' : 'text-on-surface-variant hover:text-primary hover:bg-surface-container-high' }}">
                <span class="material-symbols-outlined">engineering</span>
                <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Mechanics</span>
            </a>
        @endif
    </nav>

    <div class="pt-lg border-t border-outline-variant space-y-sm">
        <a class="flex items-center gap-md py-sm px-sm text-on-surface-variant hover:text-primary transition-colors {{ $navItemClass }}" href="#">
            <span class="material-symbols-outlined">check_circle</span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">System Status</span>
        </a>
        <a class="flex items-center gap-md py-sm px-sm text-on-surface-variant hover:text-primary transition-colors {{ $navItemClass }}" href="#">
            <span class="material-symbols-outlined">help</span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest {{ $navLabelClass }}">Help</span>
        </a>
    </div>
</aside>

{{-- TOP APP BAR --}}
<header class="fixed top-0 {{ $headerLeftClass }} right-0 bg-surface h-20 border-b border-outline-variant flex justify-between items-center px-md xl:px-lg z-40">
    <div class="flex items-center gap-lg">
        <div class="flex items-center gap-md select-none">
            <img src="{{ asset('madape-logo.PNG') }}" alt="MADAPE Logo" class="h-10 w-auto object-contain pointer-events-none" />
            <span class="font-headline-md text-headline-md font-bold tracking-tighter text-primary">{{ $portalTitle }}</span>
        </div>
        <div class="h-8 w-px bg-outline-variant"></div>
        <div class="flex items-center gap-sm">
            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
            <span class="font-label-sm text-label-sm uppercase tracking-widest text-outline">API STATUS: ONLINE / SYNCED</span>
        </div>
    </div>
    <div class="flex items-center gap-md">
        <div class="relative">
            <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary">notifications</span>
            <span class="absolute top-0 right-0 w-2 h-2 bg-secondary rounded-full border border-surface"></span>
        </div>
        <span class="material-symbols-outlined text-on-surface-variant cursor-pointer hover:text-primary">settings</span>
        @if($showLogout)
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit"
                        class="w-10 h-10 rounded-none border border-primary flex items-center justify-center bg-surface-container font-bold text-primary text-sm hover:bg-secondary hover:text-on-secondary hover:border-secondary transition-colors"
                        title="Logout">
                    <span class="material-symbols-outlined text-base">logout</span>
                </button>
            </form>
        @else
            <span class="rounded border border-outline-variant bg-white px-sm py-xs font-label-sm text-[10px] uppercase tracking-widest text-primary">
                No Auth
            </span>
        @endif
    </div>
</header>

{{-- MAIN CONTENT --}}
<main class="{{ $mainOffsetClass }} mt-20 {{ $mainPaddingClass }} grid-pattern min-h-screen">
    <div class="{{ $contentMaxWidthClass }} mx-auto space-y-lg">
        @if(session('status_success'))
            <div class="border border-secondary bg-secondary/10 px-md py-sm font-label-sm uppercase tracking-widest text-secondary">
                {{ session('status_success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="border border-error bg-error/10 px-md py-sm font-label-sm uppercase tracking-widest text-error">
                {{ $errors->first() }}
            </div>
        @endif

        @yield('content')
    </div>
</main>

@stack('scripts')
</body>
</html>
