<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600|fraunces:500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans text-slate-800 antialiased">
    <div class="min-h-screen flex flex-col justify-center items-center px-4 py-10 bg-gradient-to-b from-cyan-50 via-white to-white">

        <a href="/" class="mb-8 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-cyan-500"></span>
            <span class="text-xl tracking-tight text-slate-900" style="font-family: 'Fraunces', serif;">
                {{ config('app.name', 'Laravel') }}
            </span>
        </a>

        <div class="w-full sm:max-w-md px-6 py-8 sm:px-8 bg-white border border-cyan-100 rounded-2xl shadow-[0_8px_30px_-12px_rgba(8,145,178,0.25)]">
            {{ $slot }}
        </div>

    </div>
</body>

</html>