<a href="{{ route('catalog.show', $addon) }}" class="group bg-white rounded-2xl border border-gray-200 p-4 hover:shadow-lg hover:border-indigo-200 transition flex flex-col">
    <div class="flex items-start gap-3">
        @if ($addon->icon_url)
            <img src="{{ $addon->icon_url }}" alt="" class="w-14 h-14 rounded-2xl object-cover border border-gray-100 flex-shrink-0">
        @else
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-500 text-white flex items-center justify-center text-xl font-bold flex-shrink-0">{{ strtoupper(mb_substr($addon->name, 0, 1)) }}</div>
        @endif
        <div class="min-w-0 flex-1">
            <p class="font-semibold text-gray-900 truncate group-hover:text-indigo-600">{{ $addon->name }}</p>
            <p class="text-xs text-gray-400 truncate">{{ $addon->vendor }}@if ($addon->category) · {{ $addon->category->name }}@endif</p>
        </div>
    </div>
    <p class="text-sm text-gray-500 mt-3 line-clamp-2 flex-1 min-h-[2.5rem]">{{ $addon->tagline ?: \Illuminate\Support\Str::limit(strip_tags($addon->description), 90) ?: 'No description.' }}</p>
    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
        <span class="text-xs text-gray-400"><i class="fas fa-download mr-1"></i>{{ number_format($addon->downloads_count) }}</span>
        <span class="text-sm font-bold {{ $addon->isFree() ? 'text-emerald-600' : 'text-indigo-600' }}">{{ $addon->isFree() ? 'Free' : number_format($addon->price, 2) . ' ' . $addon->currency }}</span>
    </div>
</a>
