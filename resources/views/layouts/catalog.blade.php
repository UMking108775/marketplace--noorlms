<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Addon Marketplace') · {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 h-16 flex items-center gap-4">
            <a href="{{ url('/') }}" class="flex items-center gap-2.5 flex-shrink-0">
                <span class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white"><i class="fas fa-store"></i></span>
                <span class="font-bold text-gray-900 hidden sm:block">Noor <span class="text-indigo-600">Marketplace</span></span>
            </a>
            <form action="{{ route('catalog.index') }}" method="GET" class="flex-1 max-w-md relative">
                <i class="fas fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-gray-400"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search addons…"
                       class="w-full pl-10 pr-3 py-2 text-sm rounded-full border-gray-200 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
            </form>
            <nav class="flex items-center gap-4 text-sm flex-shrink-0">
                <a href="{{ route('catalog.index') }}" class="text-gray-600 hover:text-indigo-600 hidden sm:block">Browse</a>
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center gap-1.5 text-gray-600 hover:text-indigo-600"><i class="fas fa-gauge-high"></i><span class="hidden sm:inline">Admin</span></a>
            </nav>
        </div>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="bg-white border-t border-gray-200 mt-12">
        <div class="max-w-6xl mx-auto px-4 py-6 flex flex-col sm:flex-row items-center justify-between gap-2 text-sm text-gray-500">
            <p>© {{ now()->year }} {{ config('app.name') }} — addons for Noor LMS.</p>
            <p class="text-xs text-gray-400">Powered by the Noor addon system</p>
        </div>
    </footer>
</body>
</html>
