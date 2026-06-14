<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="font-semibold text-gray-800 leading-tight">Addons</h1>
                <p class="text-xs text-gray-500">Manage your marketplace listings</p>
            </div>
            <a href="{{ route('admin.addons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                <i class="fas fa-plus"></i> New addon
            </a>
        </div>
    </x-slot>

    {{-- Filter bar --}}
    <div class="flex items-center justify-between gap-3 mb-4 flex-wrap">
        <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm">
            @foreach (['' => 'All', 'published' => 'Published', 'draft' => 'Draft'] as $key => $label)
                @php $active = ($filters['status'] ?? '') === $key; $count = $key === '' ? $counts['all'] : $counts[$key]; @endphp
                <a href="{{ route('admin.addons.index', array_filter(['status' => $key, 'q' => $filters['q'] ?? null])) }}"
                   class="px-3 py-1.5 rounded-md font-medium {{ $active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">
                    {{ $label }} <span class="opacity-70">{{ $count }}</span>
                </a>
            @endforeach
        </div>
        <form method="GET" class="relative">
            @if (!empty($filters['status'])) <input type="hidden" name="status" value="{{ $filters['status'] }}"> @endif
            <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
            <input type="text" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search addons…"
                   class="pl-8 pr-3 py-2 text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500 w-56">
        </form>
    </div>

    @if ($addons->isEmpty())
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <div class="w-14 h-14 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center mx-auto mb-4 text-2xl"><i class="fas fa-puzzle-piece"></i></div>
            <h3 class="font-semibold text-gray-800">No addons yet</h3>
            <p class="text-sm text-gray-500 mt-1 mb-5">Upload your first addon — its details are read from the package's <code class="text-xs">addon.json</code>.</p>
            <a href="{{ route('admin.addons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-plus"></i> New addon</a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($addons as $addon)
                <a href="{{ route('admin.addons.edit', $addon) }}" class="group bg-white rounded-xl border border-gray-200 p-4 hover:shadow-md hover:border-indigo-200 transition">
                    <div class="flex items-start gap-3">
                        @if ($addon->icon_path)
                            <img src="{{ Storage::url($addon->icon_path) }}" alt="" class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-gray-100">
                        @else
                            <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold flex-shrink-0">{{ strtoupper(mb_substr($addon->name, 0, 1)) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-800 text-sm truncate group-hover:text-indigo-600">{{ $addon->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ $addon->vendor }} · v{{ $addon->latest_version }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 mt-3 line-clamp-2 min-h-[2rem]">{{ $addon->tagline ?: \Illuminate\Support\Str::limit(strip_tags($addon->description), 80) ?: 'No description yet.' }}</p>
                    <div class="flex items-center justify-between mt-3 pt-3 border-t border-gray-100">
                        @if ($addon->status === 'published')
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700"><i class="fas fa-circle text-[6px]"></i> Published</span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600"><i class="fas fa-circle text-[6px]"></i> Draft</span>
                        @endif
                        <span class="text-xs font-bold {{ $addon->isFree() ? 'text-emerald-600' : 'text-gray-800' }}">
                            {{ $addon->isFree() ? 'Free' : number_format($addon->price, 2) . ' ' . $addon->currency }}
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-5">{{ $addons->links() }}</div>
    @endif
</x-app-layout>
