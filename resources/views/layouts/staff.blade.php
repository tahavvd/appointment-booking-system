<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">

    <title>{{ $title ? $title . ' — ' : '' }}{{ config('app.name', 'Laravel') }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('logo-icon.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body
    class="font-sans text-slate-800 antialiased bg-slate-100"
    x-data="{
        sheet: { open: false, url: '', status: 'completed', client: '', service: '', when: '' },
        logoutOpen: false,
        submitting: false,
        actions: {
            completed: { title: 'Mark as completed?', text: 'The service was done. It will move to your history.', button: 'Yes, completed', iconWrap: 'bg-teal-50 text-teal-600', btn: 'bg-teal-600 hover:bg-teal-700', path: 'M5 13l4 4L19 7' },
            no_show: { title: 'Mark as no-show?', text: 'The client did not show up for this appointment.', button: 'Yes, no-show', iconWrap: 'bg-slate-100 text-slate-700', btn: 'bg-slate-900 hover:bg-slate-800', path: 'M6 18L18 6M6 6l12 12' },
            cancelled: { title: 'Cancel this appointment?', text: 'The time slot becomes free again. Call or message the client to let them know.', button: 'Yes, cancel it', iconWrap: 'bg-red-50 text-red-600', btn: 'bg-red-600 hover:bg-red-700', path: 'M12 9v3.5M12 16h.01M10.3 4.5h3.4L21 18.2a1.5 1.5 0 01-1.3 2.2H4.3A1.5 1.5 0 013 18.2L10.3 4.5z' }
        },
        get cur() { return this.actions[this.sheet.status] ?? this.actions.completed; },
        ask(url, status, client, service, when) {
            this.submitting = false;
            this.sheet = { open: true, url: url, status: status, client: client, service: service, when: when };
        }
    }"
    @keydown.escape.window="sheet.open = false; logoutOpen = false">

    {{-- Top bar --}}
    <header class="sticky top-0 z-30 bg-white/90 backdrop-blur border-b border-slate-200"
        style="padding-top: env(safe-area-inset-top)">
        <div class="mx-auto flex h-14 max-w-2xl items-center justify-between gap-3 px-4">
            <a href="{{ route('staff.dashboard') }}" class="flex items-center gap-2.5">
                <x-app-logo class="w-8 h-8" />
                <span class="text-lg tracking-tight text-slate-900" style="font-family: 'Fraunces', serif;">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </a>

            <nav class="hidden md:flex items-center gap-1 text-sm font-medium">
                <a href="{{ route('staff.dashboard') }}"
                    class="px-3 py-1.5 rounded-lg {{ request()->routeIs('staff.dashboard') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100' }}">Today</a>
                <a href="{{ route('staff.history') }}"
                    class="px-3 py-1.5 rounded-lg {{ request()->routeIs('staff.history') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100' }}">History</a>
                <a href="{{ route('staff.account') }}"
                    class="px-3 py-1.5 rounded-lg {{ request()->routeIs('staff.account') ? 'bg-teal-50 text-teal-800' : 'text-slate-600 hover:bg-slate-100' }}">Account</a>
            </nav>

            <a href="{{ route('staff.account') }}" aria-label="Account"
                class="w-9 h-9 rounded-full bg-teal-600 text-white flex items-center justify-center text-sm font-semibold">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </a>
        </div>
    </header>

    {{-- Content --}}
    <main class="mx-auto w-full max-w-2xl px-4 pt-4 pb-28 md:pb-10">

        @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
            class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-teal-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
            <p class="py-0.5">{{ session('status') }}</p>
            <button type="button" @click="show = false" aria-label="Dismiss"
                class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        @if (session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition.opacity
            class="mb-4 flex items-start justify-between gap-3 text-sm font-medium text-slate-800 bg-white border border-slate-300 border-l-4 border-l-red-500 rounded-lg pl-4 pr-2 py-2.5 shadow-sm">
            <p class="py-0.5">{{ session('error') }}</p>
            <button type="button" @click="show = false" aria-label="Dismiss"
                class="shrink-0 p-1 rounded-md text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        @endif

        {{ $slot }}
    </main>

    {{-- Bottom tab bar (phones) --}}
    <nav class="md:hidden fixed inset-x-0 bottom-0 z-30 bg-white/95 backdrop-blur border-t border-slate-200"
        style="padding-bottom: env(safe-area-inset-bottom)">
        <div class="grid grid-cols-3 h-16">
            @php
            $tabs = [
            ['route' => 'staff.dashboard', 'label' => 'Today', 'icon' => 'M8 7V3m8 4V3M4 11h16M5 5h14a1 1 0 011 1v13a1 1 0 01-1 1H5a1 1 0 01-1-1V6a1 1 0 011-1z'],
            ['route' => 'staff.history', 'label' => 'History', 'icon' => 'M12 8v4l3 2M3 12a9 9 0 109-9 9 9 0 00-6.4 2.6L3 8M3 3v5h5'],
            ['route' => 'staff.account', 'label' => 'Account', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM4 21a8 8 0 0116 0'],
            ];
            @endphp

            @foreach ($tabs as $tab)
            @php $on = request()->routeIs($tab['route']); @endphp
            <a href="{{ route($tab['route']) }}"
                class="relative flex flex-col items-center justify-center gap-1 text-xs font-semibold transition
                    {{ $on ? 'text-teal-700' : 'text-slate-500' }}">
                @if ($on)
                <span class="absolute top-0 h-1 w-10 rounded-b-full bg-teal-600"></span>
                @endif
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}" />
                </svg>
                {{ $tab['label'] }}
            </a>
            @endforeach
        </div>
    </nav>

    {{-- Confirm sheet (bottom sheet on phones) --}}
    <div x-show="sheet.open" x-cloak x-transition.opacity
        class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="sheet.open = false"></div>

        <div x-show="sheet.open" x-transition @click.stop
            class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-xl px-6 pt-4 pb-8 sm:p-6"
            style="padding-bottom: max(2rem, env(safe-area-inset-bottom))">

            <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-slate-300 sm:hidden"></div>

            <div class="flex items-start gap-4">
                <div class="shrink-0 w-11 h-11 rounded-full flex items-center justify-center" :class="cur.iconWrap">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="cur.path" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-slate-900" x-text="cur.title"></h2>
                    <p class="mt-1 text-sm text-slate-700" x-text="cur.text"></p>
                </div>
            </div>

            <div class="mt-4 rounded-xl bg-slate-100 border border-slate-200 px-4 py-3 text-sm">
                <p class="font-semibold text-slate-900" x-text="sheet.client"></p>
                <p class="font-medium text-slate-700"><span x-text="sheet.service"></span> · <span x-text="sheet.when"></span></p>
            </div>

            <form method="POST" :action="sheet.url" @submit="submitting = true" class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" :value="sheet.status">

                <button type="button" @click="sheet.open = false"
                    class="min-h-[48px] sm:min-h-0 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    Go back
                </button>
                <button type="submit" :disabled="submitting"
                    class="min-h-[48px] sm:min-h-0 px-4 py-2 text-sm font-semibold text-white rounded-xl transition disabled:opacity-60"
                    :class="cur.btn" x-text="cur.button"></button>
            </form>
        </div>
    </div>

    {{-- Logout confirm --}}
    <div x-show="logoutOpen" x-cloak x-transition.opacity
        class="fixed inset-0 z-[100] flex items-end sm:items-center justify-center sm:p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="logoutOpen = false"></div>

        <div x-show="logoutOpen" x-transition @click.stop
            class="relative w-full sm:max-w-sm bg-white rounded-t-3xl sm:rounded-2xl shadow-xl px-6 pt-4 pb-8 sm:p-6"
            style="padding-bottom: max(2rem, env(safe-area-inset-bottom))">
            <div class="mx-auto mb-4 h-1.5 w-10 rounded-full bg-slate-300 sm:hidden"></div>

            <h2 class="text-base font-semibold text-slate-900">Log out?</h2>
            <p class="mt-1 text-sm text-slate-700">You will need your email and password to sign in again.</p>

            <form method="POST" action="{{ route('logout') }}" class="mt-6 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                @csrf
                <button type="button" @click="logoutOpen = false"
                    class="min-h-[48px] sm:min-h-0 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-100 rounded-xl transition">
                    Stay signed in
                </button>
                <button type="submit"
                    class="min-h-[48px] sm:min-h-0 px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-xl transition">
                    Log out
                </button>
            </form>
        </div>
    </div>
</body>

</html>