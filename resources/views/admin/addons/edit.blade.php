<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.addons.index') }}" class="text-gray-400 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
            <h1 class="font-semibold text-gray-800 leading-tight truncate">{{ $addon->name }}</h1>
        </div>
    </x-slot>

    {{-- Header strip --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 flex flex-col sm:flex-row sm:items-center gap-4">
        @if ($addon->icon_path)
            <img src="{{ $addon->icon_url }}" alt="" class="w-16 h-16 rounded-2xl object-cover border border-gray-100 flex-shrink-0">
        @else
            <div class="w-16 h-16 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-2xl font-bold flex-shrink-0">{{ strtoupper(mb_substr($addon->name, 0, 1)) }}</div>
        @endif
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h2 class="text-lg font-bold text-gray-900">{{ $addon->name }}</h2>
                @if ($addon->status === 'published')
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Published</span>
                @else
                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-600">Draft</span>
                @endif
            </div>
            <p class="text-sm text-gray-500">{{ $addon->vendor }}/{{ $addon->package_name }} · v{{ $addon->latest_version }} · {{ $addon->isFree() ? 'Free' : number_format($addon->price, 2) . ' ' . $addon->currency }}</p>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.addons.publish', $addon) }}">
                @csrf
                @if ($addon->status === 'published')
                    <button class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-700 hover:bg-gray-50"><i class="fas fa-eye-slash mr-1"></i> Unpublish</button>
                @else
                    <button class="px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700"><i class="fas fa-rocket mr-1"></i> Publish</button>
                @endif
            </form>
            <form method="POST" action="{{ route('admin.addons.destroy', $addon) }}" onsubmit="return confirm('Delete this addon and all its versions? This cannot be undone.')">
                @csrf @method('DELETE')
                <button class="w-9 h-9 rounded-lg border border-gray-200 text-gray-400 hover:text-red-600 hover:border-red-200" title="Delete"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Left: details + screenshots --}}
        <div class="lg:col-span-2 space-y-4">
            <form method="POST" action="{{ route('admin.addons.update', $addon) }}" enctype="multipart/form-data" class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ paid: {{ $addon->is_paid ? 'true' : 'false' }} }">
                @csrf @method('PUT')
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Details</h3>
                <div class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                            <select name="category_id" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">— none —</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}" @selected($addon->category_id == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tagline</label>
                            <input type="text" name="tagline" maxlength="160" value="{{ old('tagline', $addon->tagline) }}" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Description</label>
                        <textarea name="description" rows="5" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">{{ old('description', $addon->description) }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Replace icon</label>
                        <input type="file" name="icon" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Pricing</label>
                        <div class="flex gap-2 mb-3">
                            <label class="flex-1 cursor-pointer"><input type="radio" name="is_paid" value="0" class="sr-only" @click="paid=false" {{ $addon->is_paid ? '' : 'checked' }}>
                                <div class="border rounded-lg px-4 py-2 text-sm text-center" :class="!paid ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'">Free</div></label>
                            <label class="flex-1 cursor-pointer"><input type="radio" name="is_paid" value="1" class="sr-only" @click="paid=true" {{ $addon->is_paid ? 'checked' : '' }}>
                                <div class="border rounded-lg px-4 py-2 text-sm text-center" :class="paid ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'">Paid</div></label>
                        </div>
                        <div x-show="paid" x-cloak class="grid grid-cols-2 gap-4">
                            <input type="number" name="price" step="0.01" min="0" value="{{ old('price', $addon->price) }}" placeholder="Price" class="text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                            <input type="text" name="currency" maxlength="10" value="{{ old('currency', $addon->currency) }}" placeholder="USD" class="text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                    <div><button class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-floppy-disk mr-1"></i> Save details</button></div>
                </div>
            </form>

            {{-- Screenshots --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-800">Screenshots</h3>
                    <form method="POST" action="{{ route('admin.addons.screenshots.store', $addon) }}" enctype="multipart/form-data" class="flex items-center gap-2">
                        @csrf
                        <input type="file" name="screenshots[]" accept="image/*" multiple required class="hidden" id="shotInput" onchange="this.form.submit()">
                        <label for="shotInput" class="cursor-pointer inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700"><i class="fas fa-plus"></i> Add</label>
                    </form>
                </div>
                @if ($addon->screenshots->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-6"><i class="fas fa-image text-xl block mb-1 text-gray-300"></i>No screenshots yet.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach ($addon->screenshots as $shot)
                            <div class="relative group rounded-lg overflow-hidden border border-gray-100">
                                <img src="{{ $shot->url }}" alt="" class="w-full h-24 object-cover">
                                <form method="POST" action="{{ route('admin.addons.screenshots.destroy', [$addon, $shot]) }}" class="absolute top-1 right-1">
                                    @csrf @method('DELETE')
                                    <button class="w-6 h-6 rounded-md bg-black/50 text-white text-xs opacity-0 group-hover:opacity-100 transition" title="Remove"><i class="fas fa-xmark"></i></button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Right: versions --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Versions</h3>
                <div class="space-y-2 mb-4">
                    @foreach ($addon->versions as $v)
                        <div class="border border-gray-100 rounded-lg p-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-semibold text-sm text-gray-800">v{{ $v->version }}</span>
                                    @if ($v->is_latest)<span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-indigo-100 text-indigo-600">latest</span>@endif
                                </div>
                                <form method="POST" action="{{ route('admin.addons.versions.destroy', [$addon, $v]) }}" onsubmit="return confirm('Delete version {{ $v->version }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-gray-300 hover:text-red-500 text-xs" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-1">{{ $v->released_at?->format('d M Y') }} · {{ number_format($v->file_size / 1024, 0) }} KB · {{ $v->downloads_count }} downloads</p>
                            @if ($v->changelog)<p class="text-xs text-gray-500 mt-1.5 line-clamp-2">{{ $v->changelog }}</p>@endif
                        </div>
                    @endforeach
                </div>
                <form method="POST" action="{{ route('admin.addons.versions.store', $addon) }}" enctype="multipart/form-data" class="border-t border-gray-100 pt-4 space-y-2">
                    @csrf
                    <p class="text-xs font-semibold text-gray-700">Upload new version</p>
                    <input type="file" name="zip" accept=".zip" required class="block w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-2.5 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600">
                    <textarea name="changelog" rows="2" placeholder="What changed? (changelog)" class="w-full text-xs rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    <button class="w-full px-3 py-2 rounded-lg bg-gray-800 text-white text-xs font-semibold hover:bg-gray-900"><i class="fas fa-upload mr-1"></i> Upload version</button>
                    <p class="text-[10px] text-gray-400">Version number is read from the package's addon.json.</p>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
