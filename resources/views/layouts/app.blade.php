<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'Dashboard') . ' · ' . config('app.name') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>[x-cloak]{display:none !important;}</style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-800">
<div x-data="{ open: false }" class="min-h-screen">

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-30 w-64 bg-slate-900 text-slate-300 flex flex-col transform transition-transform duration-200 lg:translate-x-0"
           :class="open ? 'translate-x-0' : '-translate-x-full'">
        <div class="h-16 flex items-center gap-2.5 px-5 border-b border-slate-800">
            <span class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white"><i class="fas fa-store"></i></span>
            <span class="font-semibold text-white leading-tight">Noor<br class="hidden"><span class="text-indigo-400"> Marketplace</span></span>
        </div>

        @php
            $nav = [
                ['Dashboard',  'dashboard',                'fa-gauge-high'],
                ['Addons',     'admin.addons.index',       'fa-puzzle-piece'],
                ['Categories', 'admin.categories.index',   'fa-tags'],
                ['Licenses',   'admin.licenses.index',     'fa-key'],
                ['Sites',      'admin.sites.index',        'fa-server'],
                ['Reviews',    'admin.reviews.index',      'fa-star'],
            ];
        @endphp
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
            @foreach ($nav as [$label, $routeName, $icon])
                @php
                    $exists = \Illuminate\Support\Facades\Route::has($routeName);
                    $url = $exists ? route($routeName) : '#';
                    $active = $exists && request()->routeIs(\Illuminate\Support\Str::beforeLast($routeName, '.') . '*');
                @endphp
                <a href="{{ $url }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ $active ? 'bg-indigo-600 text-white' : 'hover:bg-slate-800 hover:text-white' }}">
                    <i class="fas {{ $icon }} w-5 text-center"></i>
                    <span>{{ $label }}</span>
                    @unless ($exists)
                        <span class="ml-auto text-[10px] uppercase tracking-wide text-slate-500 bg-slate-800 px-1.5 py-0.5 rounded">soon</span>
                    @endunless
                </a>
            @endforeach
        </nav>

        <div class="px-3 py-3 border-t border-slate-800">
            <div class="flex items-center gap-2.5 px-2">
                <div class="w-8 h-8 rounded-full bg-indigo-600 flex items-center justify-center text-xs font-semibold text-white flex-shrink-0">
                    {{ strtoupper(mb_substr(auth()->user()->name ?? '?', 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-sm text-white truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- Mobile backdrop --}}
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 bg-black/40 z-20 lg:hidden"></div>

    {{-- Main --}}
    <div class="lg:pl-64">
        <header class="h-16 bg-white border-b border-gray-200 flex items-center gap-3 px-4 sm:px-6 sticky top-0 z-10">
            <button @click="open = !open" class="lg:hidden text-gray-500 hover:text-gray-800"><i class="fas fa-bars text-lg"></i></button>
            <div class="flex-1 min-w-0">
                @isset($header)
                    {{ $header }}
                @else
                    <h1 class="font-semibold text-gray-800 truncate">{{ $title ?? 'Dashboard' }}</h1>
                @endisset
            </div>
            <a href="{{ url('/') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-800">
                <i class="fas fa-arrow-up-right-from-square"></i> View site
            </a>
            <a href="{{ route('profile.edit') }}" class="text-sm text-gray-500 hover:text-gray-800" title="Profile"><i class="fas fa-gear"></i></a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-1.5 text-sm text-gray-600 hover:text-red-600"><i class="fas fa-right-from-bracket"></i><span class="hidden sm:inline">Logout</span></button>
            </form>
        </header>

        <main class="p-4 sm:p-6">
            {{ $slot }}
        </main>
    </div>
</div>
</body>
</html>
