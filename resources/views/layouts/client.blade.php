<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-800 antialiased" style="background-color:#072A2E;">

    @if (session()->has('booking.client_id'))
    {{-- Desktop top navbar --}}
    <nav class="hidden md:block sticky top-0 z-40 backdrop-blur-xl bg-[#072A2E]/80 border-b border-white/10">
        <div class="max-w-6xl mx-auto px-6 h-16 flex items-center justify-between">

            <a href="{{ route('booking.service') }}" class="text-lg text-white tracking-tight" style="font-family:'Fraunces',serif;">
                {{ config('app.name') }}
            </a>

            <div class="flex items-center gap-1 bg-white/5 border border-white/10 rounded-full p-1">
                <a href="{{ route('booking.service') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ request()->routeIs('booking.service') ? 'bg-cyan-600 text-white shadow-sm' : 'text-white/60 hover:text-white' }}">
                    Services
                </a>
                <a href="{{ route('booking.my-appointments') }}"
                    class="px-4 py-1.5 rounded-full text-sm font-medium transition {{ request()->routeIs('booking.my-appointments') ? 'bg-cyan-600 text-white shadow-sm' : 'text-white/60 hover:text-white' }}">
                    My Appointment
                </a>
            </div>

            <form method="POST" action="{{ route('booking.logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-sm font-medium text-white/60 hover:text-red-400 transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                    </svg>
                    Log out
                </button>
            </form>

        </div>
    </nav>
    @endif

    <div class="min-h-screen {{ session()->has('booking.client_id') ? 'pb-28 md:pb-0' : '' }}" style="background:radial-gradient(120% 60% at 50% 0%, rgba(103,232,249,0.14), transparent 60%);">

        {{ $slot }}

        @if (session()->has('booking.client_id'))
        {{-- Mobile bottom nav --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 px-4" style="padding-bottom: calc(env(safe-area-inset-bottom) + 1rem);">
            <div class="max-w-md mx-auto bg-[#0A3338]/90 backdrop-blur-xl border border-white/10 rounded-2xl shadow-[0_20px_40px_-15px_rgba(0,0,0,0.5)] flex items-stretch overflow-hidden mt-4">

                <a href="{{ route('booking.service') }}"
                    class="flex-1 flex flex-col items-center gap-1 py-3 transition {{ request()->routeIs('booking.service') ? 'text-cyan-300' : 'text-white/40' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5L12 3l9 6.5V20a1 1 0 01-1 1h-5v-6H9v6H4a1 1 0 01-1-1V9.5z" />
                    </svg>
                    <span class="text-[11px] font-medium">Services</span>
                </a>

                <a href="{{ route('booking.my-appointments') }}"
                    class="flex-1 flex flex-col items-center gap-1 py-3 transition {{ request()->routeIs('booking.my-appointments') ? 'text-cyan-300' : 'text-white/40' }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <rect x="3" y="5" width="18" height="16" rx="2" />
                        <path stroke-linecap="round" d="M8 3v4M16 3v4M3 10h18" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.5 14.5l2 2 4-4" />
                    </svg>
                    <span class="text-[11px] font-medium">Appointment</span>
                </a>

                <form method="POST" action="{{ route('booking.logout') }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full h-full flex flex-col items-center gap-1 py-3 text-white/40 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9" />
                        </svg>
                        <span class="text-[11px] font-medium">Log out</span>
                    </button>
                </form>

            </div>
        </nav>
        @endif

    </div>
</body>

</html>