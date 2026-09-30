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

<body
    class="font-sans text-slate-800 antialiased bg-slate-50"
    x-data="{ sidebarOpen: false, logoutOpen: false }">

    {{-- Mobile backdrop --}}
    <div
        x-show="sidebarOpen"
        x-cloak
        class="fixed inset-0 bg-slate-900/40 z-30 lg:hidden"
        @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside
        class="fixed inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col transform transition-transform duration-200 ease-in-out -translate-x-full lg:translate-x-0"
        :class="{ 'translate-x-0': sidebarOpen }">

        {{-- Logo --}}
        <div class="h-16 flex items-center gap-5 px-1 border-b border-slate-100">
            <x-app-logo class="w-7 h-7" />

            <span
                class="text-lg tracking-tight text-slate-900"
                style="font-family: 'Fraunces', serif;">
                {{ config('app.name', 'Laravel') }}
            </span>
        </div>

        {{-- Navigation --}}
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

            @php
            $exists = Route::has($item['route']);
            @endphp

            @if ($exists)

            <a
                href="{{ route($item['route']) }}"
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition
                            {{ request()->routeIs($item['route'])
                                ? 'bg-teal-50 text-teal-800'
                                : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <x-admin-nav-icon
                    :name="$item['icon']"
                    class="w-5 h-5 shrink-0" />

                {{ $item['label'] }}
            </a>

            @else

            <span
                class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium text-slate-300 cursor-not-allowed"
                title="Coming soon">
                <x-admin-nav-icon
                    :name="$item['icon']"
                    class="w-5 h-5 shrink-0" />

                {{ $item['label'] }}
            </span>

            @endif

            @endforeach

        </nav>

        {{-- User section --}}
        <div class="p-4 border-t border-slate-100">

            <div class="flex items-center gap-3 px-2 mb-3">

                <div class="w-8 h-8 rounded-full bg-teal-600 text-white flex items-center justify-center text-xs font-semibold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>

                <div class="min-w-0">

                    <p class="text-sm font-medium text-slate-900 truncate">
                        {{ auth()->user()->name }}
                    </p>

                    <p class="text-xs text-slate-500 capitalize">
                        {{ auth()->user()->role }}
                    </p>

                </div>

            </div>

            {{-- Logout form --}}
            <form
                id="logout-form"
                method="POST"
                action="{{ route('logout') }}">
                @csrf

                <button
                    type="button"
                    @click="logoutOpen = true"
                    class="w-full flex items-center gap-2 px-2 py-1.5 text-sm text-slate-500 hover:text-red-600 transition">
                    <svg
                        class="w-4 h-4"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>

                    Log out
                </button>
            </form>

        </div>

    </aside>

    {{-- Logout confirmation modal --}}
    <div
        x-show="logoutOpen"
        x-cloak
        x-transition.opacity
        class="fixed inset-0 z-[100] flex items-center justify-center p-4"
        @keydown.escape.window="logoutOpen = false">

        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
            @click="logoutOpen = false"></div>

        {{-- Modal --}}
        <div
            x-show="logoutOpen"
            x-transition
            class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl border border-slate-200 p-6"
            @click.stop>

            {{-- Modal content --}}
            <div class="flex items-start gap-4">

                {{-- Warning icon --}}
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-50 flex items-center justify-center">

                    <svg
                        class="w-5 h-5 text-red-600"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="1.8">
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M12 9v3.5M12 16h.01M10.3 4.5h3.4L21 18.2a1.5 1.5 0 01-1.3 2.2H4.3A1.5 1.5 0 013 18.2L10.3 4.5z" />
                    </svg>

                </div>

                <div>

                    <h2 class="text-base font-semibold text-slate-900">
                        Log out?
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Are you sure you want to log out of your account?
                    </p>

                </div>

            </div>

            {{-- Modal buttons --}}
            <div class="mt-6 flex justify-end gap-3">

                {{-- Cancel --}}
                <button
                    type="button"
                    @click="logoutOpen = false"
                    class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg transition">
                    Cancel
                </button>

                {{-- Confirm logout --}}
                <button
                    type="submit"
                    form="logout-form"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition">
                    Log out
                </button>

            </div>

        </div>

    </div>

    {{-- Main column --}}
    <div class="lg:pl-64">

        {{-- Header --}}
        <header
            class="sticky top-0 z-20 h-16 bg-white/80 backdrop-blur border-b border-slate-200 flex items-center gap-4 px-4 sm:px-6">

            {{-- Mobile menu button --}}
            <button
                @click="sidebarOpen = true"
                class="lg:hidden p-2 -ml-2 text-slate-500 hover:text-slate-900">
                <svg
                    class="w-6 h-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.8">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <h1 class="text-lg font-semibold text-slate-900">
                {{ $title }}
            </h1>

        </header>

        {{-- Page content --}}
        <main class="p-4 sm:p-6">
            {{ $slot }}
        </main>

    </div>

</body>

</html>