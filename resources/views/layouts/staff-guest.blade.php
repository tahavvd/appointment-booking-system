<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} — Staff & Admin</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-b from-teal-50 via-white to-white">

        <div class="mb-6 flex flex-col items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#2F8F7A]"></span>
                <span class="text-xl tracking-tight text-slate-900" style="font-family: 'Fraunces', serif;">
                    {{ config('app.name', 'Laravel') }}
                </span>
            </div>

            <div class="flex items-center gap-1.5 px-3 py-1 rounded-full border border-[#2F8F7A]/40 text-[#1F5C50] text-xs font-medium tracking-wide">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 1a4 4 0 00-4 4v2H5a2 2 0 00-2 2v7a2 2 0 002 2h10a2 2 0 002-2V9a2 2 0 00-2-2h-1V5a4 4 0 00-4-4zm-2 6V5a2 2 0 114 0v2H8z" clip-rule="evenodd" />
                </svg>
                Staff &amp; Admin Sign-in
            </div>

            <p class="text-sm text-slate-500 text-center max-w-xs">
                This area is for salon staff and administrators only.
            </p>
        </div>

        <div class="w-full sm:max-w-md px-6 py-8 sm:px-8 bg-white border border-slate-200 rounded-2xl shadow-[0_8px_30px_-12px_rgba(15,23,42,0.12)]">
            {{ $slot }}
        </div>

        <p class="mt-6 text-sm text-slate-500">
            Trying to book an appointment?
            <a href="{{ route('booking.start') }}" class="text-cyan-600 hover:text-cyan-700 font-medium">
                Go to booking →
            </a>
        </p>

    </div>
</body>

</html>