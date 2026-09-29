<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-800 antialiased bg-slate-50" x-data="{ sidebarOpen: false }">

    {{-- Mobile backdrop --}}
    <div x-show="sidebarOpen" x-cloak
        class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden"
        @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transform transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0"
        :class="{ 'translate-x-0': sidebarOpen }">

        <div class="h-16 flex items-center gap-2 px-6 border-b border-slate-100">
            <span class="w-2.5 h-2.5 rounded-full bg-teal-600"></span>
            <span class="text-lg tracking-tight text-slate-900" style="font-family: 'Fraunces', serif;">
                {{ config('app.name', 'Laravel') }}
            </span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @php
            $navItems = [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Services', 'route' => 'admin.services.index', 'icon' => 'scissors'],
            ['label' => 'Staff', 'route' => 'admin.staff.index', 'icon' => 'users'],
            ['label' => 'Schedules', 'route' => 'admin.schedules.index', 'icon' => 'calendar'],
            ['label' => 'Appointments', 'route' => 'admin.appointments.index', 'icon' => 'clipboard'],
            ];
            @endphp

            @foreach ($navItems as $item)
            @php $exists = Route::has($item['route']); @endphp
            @if ($exists)
            <a href="{{ route($item['route']) }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs($item['route']) ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <x-admin-nav-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
                {{ $item['label'] }}
            </a>
            @else
            <span class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-300 cursor-not-allowed"
                title="Coming soon">
                <x-admin-nav-icon :name="$item['icon']" class="w-5 h-5 shrink-0" />
                {{ $item['label'] }}
            </span>
            @endif
            @endforeach
        </nav>

        <div class="p-4 border-t border-slate-100">
            <div class="flex items-center gap-3 px-2 mb-3">
                <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-500 capitalize">{{ auth()->user()->role }}</p>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-2 px-2 py-1.5 text-sm text-slate-500 hover:text-red-600 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                    Log out
                </button>
            </form>
        </div>
    </aside>

    {{-- Main column --}}
    <div class="lg:pl-64">
        <header class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center gap-4 px-4 sm:px-6">
            <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-900">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <h1 class="text-lg font-semibold text-slate-900">{{ $title }}</h1>
        </header>

        <main class="p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>

</body>

</html>