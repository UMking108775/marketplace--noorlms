<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-semibold text-gray-800 leading-tight">Dashboard</h1>
            <p class="text-xs text-gray-500">Overview of your addon marketplace</p>
        </div>
    </x-slot>

    @php
        $cards = [
            ['Addons',     $stats['addons'],     'fa-puzzle-piece', 'bg-indigo-100 text-indigo-600'],
            ['Published',  $stats['published'],  'fa-circle-check', 'bg-emerald-100 text-emerald-600'],
            ['Categories', $stats['categories'], 'fa-tags',         'bg-amber-100 text-amber-600'],
            ['Licenses',   $stats['licenses'],   'fa-key',          'bg-violet-100 text-violet-600'],
            ['Sites',      $stats['sites'],      'fa-server',        'bg-sky-100 text-sky-600'],
            ['Reviews',    $stats['reviews'],    'fa-star',          'bg-rose-100 text-rose-600'],
        ];
    @endphp

    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @foreach ($cards as [$label, $value, $icon, $color])
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center mb-3 {{ $color }}"><i class="fas {{ $icon }}"></i></div>
                <p class="text-2xl font-bold text-gray-900 leading-none">{{ number_format($value) }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Getting started --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-1">Getting started</h2>
            <p class="text-sm text-gray-500 mb-5">Your marketplace is ready. Here's the path to your first published addon.</p>
            <ol class="space-y-4">
                @foreach ([
                    ['Add categories', 'Group addons (Marketing, Analytics, UI…). A starter set is already seeded.', $stats['categories'] > 0],
                    ['Upload an addon', 'Drop an addon .zip — name, vendor, version and description are read from its addon.json. Add an icon, screenshots and a price.', $stats['addons'] > 0],
                    ['Publish it', 'Flip the listing from Draft to Published so LMS installs can find it.', $stats['published'] > 0],
                    ['Connect the LMS', 'Point your LMS Marketplace page at this site to browse and install.', $stats['sites'] > 0],
                ] as $i => [$t, $d, $done])
                    <li class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0 {{ $done ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-500' }}">
                            @if ($done)<i class="fas fa-check"></i>@else{{ $i + 1 }}@endif
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $t }}</p>
                            <p class="text-xs text-gray-500 mt-0.5 leading-snug">{{ $d }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>
            <p class="text-xs text-gray-400 mt-5"><i class="fas fa-circle-info mr-1"></i>Upload, publishing and the LMS connection arrive in the next build phases.</p>
        </div>

        {{-- Quick facts --}}
        <div class="bg-white rounded-xl border border-gray-200 p-6">
            <h2 class="text-base font-semibold text-gray-900 mb-4">At a glance</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-gray-500">Published / total</dt><dd class="font-semibold text-gray-800">{{ $stats['published'] }} / {{ $stats['addons'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Paid addons</dt><dd class="font-semibold text-gray-800">{{ $stats['paid'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Active licenses</dt><dd class="font-semibold text-gray-800">{{ $stats['licenses'] }}</dd></div>
                <div class="flex justify-between"><dt class="text-gray-500">Connected LMS sites</dt><dd class="font-semibold text-gray-800">{{ $stats['sites'] }}</dd></div>
            </dl>
        </div>
    </div>
</x-app-layout>
