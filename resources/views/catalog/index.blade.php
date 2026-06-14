@extends('layouts.catalog')
@section('title', 'Browse addons')

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-900">Browse addons</h1>
    <p class="text-sm text-gray-500 mb-6">{{ $addons->total() }} addon(s)@if (request('q')) matching “{{ request('q') }}”@endif</p>

    {{-- Filters --}}
    <div class="flex flex-wrap items-center gap-3 mb-6">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm">
            @foreach (['' => 'All', 'free' => 'Free', 'paid' => 'Paid'] as $k => $l)
                @php $active = (string) request('type') === $k; @endphp
                <a href="{{ route('catalog.index', array_filter(['type' => $k, 'q' => request('q'), 'category' => request('category')])) }}"
                   class="px-3 py-1.5 rounded-md font-medium {{ $active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ $l }}</a>
            @endforeach
        </div>
        <form method="GET" class="flex items-center gap-2">
            @if (request('q'))<input type="hidden" name="q" value="{{ request('q') }}">@endif
            @if (request('type'))<input type="hidden" name="type" value="{{ request('type') }}">@endif
            <select name="category" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">All categories</option>
                @foreach ($categories as $c)
                    <option value="{{ $c->slug }}" @selected(request('category') === $c->slug)>{{ $c->name }}</option>
                @endforeach
            </select>
            <select name="sort" onchange="this.form.submit()" class="text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                @foreach (['' => 'Featured', 'popular' => 'Most downloaded', 'rating' => 'Top rated', 'price_asc' => 'Price: low → high', 'price_desc' => 'Price: high → low'] as $k => $l)
                    <option value="{{ $k }}" @selected((string) request('sort') === $k)>{{ $l }}</option>
                @endforeach
            </select>
        </form>
        @if (request()->hasAny(['q', 'type', 'category', 'sort']))
            <a href="{{ route('catalog.index') }}" class="text-sm text-gray-500 hover:text-gray-800">Clear</a>
        @endif
    </div>

    @if ($addons->isEmpty())
        <div class="text-center py-16 text-gray-400 bg-white rounded-2xl border border-gray-100">
            <i class="fas fa-magnifying-glass text-3xl mb-2"></i>
            <p>No addons match your filters.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($addons as $addon) @include('catalog._card') @endforeach
        </div>
        <div class="mt-6">{{ $addons->links() }}</div>
    @endif
</div>
@endsection
