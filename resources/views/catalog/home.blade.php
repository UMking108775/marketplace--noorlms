@extends('layouts.catalog')
@section('title', 'Addon Marketplace')

@section('content')
    <section class="bg-gradient-to-b from-indigo-50 to-gray-50 border-b border-gray-100">
        <div class="max-w-6xl mx-auto px-4 py-14 text-center">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900 tracking-tight">Extend your LMS with addons</h1>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">Browse {{ $count }} free &amp; premium addons and install them straight from your LMS admin panel.</p>
            @if ($categories->isNotEmpty())
                <div class="flex flex-wrap justify-center gap-2 mt-6">
                    @foreach ($categories as $c)
                        <a href="{{ route('catalog.category', $c) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-white border border-gray-200 text-sm text-gray-600 hover:border-indigo-300 hover:text-indigo-600">
                            @if ($c->icon)<i class="fas {{ $c->icon }} text-xs"></i>@endif {{ $c->name }} <span class="text-gray-400">{{ $c->addons_count }}</span>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 py-10">
        @if ($featured->isNotEmpty())
            <h2 class="text-lg font-bold text-gray-900 mb-4"><i class="fas fa-star text-amber-400 mr-1"></i> Featured</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-10">
                @foreach ($featured as $addon) @include('catalog._card') @endforeach
            </div>
        @endif

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-900">Newest addons</h2>
            <a href="{{ route('catalog.index') }}" class="text-sm text-indigo-600 hover:underline">Browse all <i class="fas fa-arrow-right text-xs"></i></a>
        </div>
        @if ($recent->isEmpty())
            <div class="text-center py-16 text-gray-400 bg-white rounded-2xl border border-gray-100">
                <i class="fas fa-box-open text-3xl mb-2"></i>
                <p>No published addons yet — publish one from the admin panel.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ($recent as $addon) @include('catalog._card') @endforeach
            </div>
        @endif
    </div>
@endsection
