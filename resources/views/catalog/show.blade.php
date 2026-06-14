@extends('layouts.catalog')
@section('title', $addon->name)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-8">
    <a href="{{ route('catalog.index') }}" class="text-sm text-gray-500 hover:text-gray-800 mb-4 inline-flex items-center gap-1.5"><i class="fas fa-arrow-left"></i> Back to browse</a>

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col sm:flex-row items-start gap-5">
        @if ($addon->icon_url)
            <img src="{{ $addon->icon_url }}" alt="" class="w-20 h-20 rounded-2xl object-cover border border-gray-100 flex-shrink-0">
        @else
            <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white flex items-center justify-center text-3xl font-bold flex-shrink-0">{{ strtoupper(mb_substr($addon->name, 0, 1)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <h1 class="text-2xl font-bold text-gray-900">{{ $addon->name }}</h1>
            <p class="text-sm text-gray-500">by {{ $addon->vendor }}@if ($addon->category) · <a href="{{ route('catalog.category', $addon->category) }}" class="text-indigo-600 hover:underline">{{ $addon->category->name }}</a>@endif</p>
            @if ($addon->tagline)<p class="text-gray-600 mt-2">{{ $addon->tagline }}</p>@endif
            <div class="flex flex-wrap items-center gap-4 mt-3 text-sm text-gray-500">
                <span><i class="fas fa-code-branch mr-1"></i>v{{ $addon->latest_version }}</span>
                <span><i class="fas fa-download mr-1"></i>{{ number_format($addon->downloads_count) }} downloads</span>
                @if ($addon->rating_count > 0)<span class="inline-flex items-center gap-1.5">@include('catalog._stars', ['rating' => $addon->rating_avg]) {{ number_format($addon->rating_avg, 1) }} ({{ $addon->rating_count }})</span>@endif
                @if ($addon->min_lms_version)<span><i class="fas fa-circle-check mr-1"></i>LMS {{ $addon->min_lms_version }}+</span>@endif
            </div>
        </div>
        <div class="text-right flex-shrink-0">
            <p class="text-2xl font-bold {{ $addon->isFree() ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $addon->isFree() ? 'Free' : number_format($addon->price, 2) . ' ' . $addon->currency }}</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6 mt-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Screenshots --}}
            @if ($addon->screenshots->isNotEmpty())
                <div class="flex gap-3 overflow-x-auto pb-2">
                    @foreach ($addon->screenshots as $shot)
                        <a href="{{ $shot->url }}" target="_blank" class="flex-shrink-0">
                            <img src="{{ $shot->url }}" alt="" class="h-56 rounded-xl border border-gray-200 object-cover">
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Description --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-3">About this addon</h2>
                @if ($addon->description)
                    <div class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $addon->description }}</div>
                @else
                    <p class="text-sm text-gray-400">No description provided.</p>
                @endif
            </div>

            {{-- Versions --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-base font-bold text-gray-900 mb-3">Version history</h2>
                <div class="space-y-3">
                    @foreach ($addon->versions as $v)
                        <div class="flex items-start gap-3 pb-3 border-b border-gray-100 last:border-0 last:pb-0">
                            <span class="text-xs font-semibold px-2 py-0.5 rounded {{ $v->is_latest ? 'bg-indigo-100 text-indigo-600' : 'bg-gray-100 text-gray-500' }}">v{{ $v->version }}</span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs text-gray-400">{{ $v->released_at?->format('d M Y') }} · {{ number_format($v->file_size / 1024, 0) }} KB</p>
                                @if ($v->changelog)<p class="text-sm text-gray-600 mt-1">{{ $v->changelog }}</p>@endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Reviews --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6" id="reviews">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-base font-bold text-gray-900">Reviews</h2>
                    @if ($addon->rating_count > 0)
                        <div class="flex items-center gap-2">@include('catalog._stars', ['rating' => $addon->rating_avg])<span class="text-sm font-semibold text-gray-700">{{ number_format($addon->rating_avg, 1) }}</span><span class="text-xs text-gray-400">· {{ $addon->rating_count }} review(s)</span></div>
                    @endif
                </div>

                @if (session('reviewed'))
                    <div class="mb-4 px-4 py-2.5 rounded-lg bg-emerald-50 text-emerald-700 text-sm"><i class="fas fa-circle-check mr-1"></i>Thanks — your review was posted.</div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 px-4 py-2.5 rounded-lg bg-red-50 text-red-700 text-sm">{{ $errors->first() }}</div>
                @endif

                @forelse ($addon->reviews as $rev)
                    <div class="py-3 border-b border-gray-100 last:border-0">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-semibold text-gray-800">{{ $rev->reviewer_name }}</p>
                            @include('catalog._stars', ['rating' => $rev->rating])
                        </div>
                        @if ($rev->comment)<p class="text-sm text-gray-600 mt-1">{{ $rev->comment }}</p>@endif
                        <p class="text-[11px] text-gray-400 mt-1">{{ $rev->created_at?->diffForHumans() }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-400">No reviews yet — be the first.</p>
                @endforelse

                <form method="POST" action="{{ route('catalog.reviews.store', $addon) }}" class="mt-5 pt-5 border-t border-gray-100" x-data="{ rating: 5 }">
                    @csrf
                    <p class="text-sm font-semibold text-gray-800 mb-2">Write a review</p>
                    <div class="flex items-center gap-1 mb-3 text-xl">
                        @for ($i = 1; $i <= 5; $i++)
                            <button type="button" @click="rating = {{ $i }}" class="focus:outline-none"><i class="fas fa-star" :class="rating >= {{ $i }} ? 'text-amber-400' : 'text-gray-300'"></i></button>
                        @endfor
                        <input type="hidden" name="rating" :value="rating">
                    </div>
                    <input type="text" name="reviewer_name" required maxlength="80" placeholder="Your name" class="w-full sm:w-1/2 text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 mb-3">
                    <textarea name="comment" rows="3" maxlength="1500" placeholder="Your experience with this addon…" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 mb-3"></textarea>
                    <div><button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">Post review</button></div>
                </form>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="bg-indigo-50 rounded-2xl border border-indigo-100 p-5">
                <h3 class="font-bold text-gray-900 mb-2"><i class="fas fa-download text-indigo-600 mr-1"></i> Get this addon</h3>
                <p class="text-sm text-gray-600">Open <strong>Marketplace</strong> in your Noor LMS admin panel, find this addon and click <strong>Install</strong> — it downloads and installs automatically.</p>
                @if (! $addon->isFree())
                    <p class="text-xs text-indigo-700 mt-3 bg-indigo-100 rounded-lg px-3 py-2"><i class="fas fa-key mr-1"></i> Paid addon — you'll enter your license key during install.</p>
                @endif
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <dl class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><dt class="text-gray-500">Latest version</dt><dd class="font-semibold text-gray-800">{{ $addon->latest_version }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Min LMS version</dt><dd class="font-semibold text-gray-800">{{ $addon->min_lms_version ?? 'any' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Downloads</dt><dd class="font-semibold text-gray-800">{{ number_format($addon->downloads_count) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Category</dt><dd class="font-semibold text-gray-800">{{ $addon->category?->name ?? '—' }}</dd></div>
                    <div class="flex justify-between"><dt class="text-gray-500">Updated</dt><dd class="font-semibold text-gray-800">{{ $addon->updated_at?->format('d M Y') }}</dd></div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
