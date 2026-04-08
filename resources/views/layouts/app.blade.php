<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Task Manager') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-900 text-slate-100">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Main content --}}
        <div class="flex-1 ml-56">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="bg-green-900/40 border-b border-green-800 text-green-300 text-sm px-6 py-3">
                    ✅ {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-900/40 border-b border-red-800 text-red-300 text-sm px-6 py-3">
                    ❌ {{ session('error') }}
                </div>
            @endif

            {{-- Page header slot --}}
            @isset($header)
                <header class="bg-slate-800/50 border-b border-slate-700 px-6 py-4">
                    {{ $header }}
                </header>
            @endisset

            {{-- Page content --}}
            <main class="p-6">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>