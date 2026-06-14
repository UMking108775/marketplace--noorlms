<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.addons.index') }}" class="text-gray-400 hover:text-gray-700"><i class="fas fa-arrow-left"></i></a>
            <div>
                <h1 class="font-semibold text-gray-800 leading-tight">New addon</h1>
                <p class="text-xs text-gray-500">Upload a package — its name, vendor, version &amp; description come from addon.json</p>
            </div>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('admin.addons.store') }}" enctype="multipart/form-data" class="max-w-3xl" x-data="{ paid: {{ old('is_paid') ? 'true' : 'false' }}, zip: '', shots: 0 }">
        @csrf

        {{-- Package --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-1">Package</h2>
            <p class="text-xs text-gray-500 mb-3">The <code class="text-[11px]">.zip</code> you'd distribute. Metadata is read from its <code class="text-[11px]">addon.json</code>.</p>
            <label class="flex flex-col items-center justify-center border-2 border-dashed border-gray-200 rounded-xl py-8 cursor-pointer hover:border-indigo-300 hover:bg-indigo-50/40 transition">
                <i class="fas fa-file-zipper text-2xl text-indigo-500 mb-2"></i>
                <span class="text-sm text-gray-600" x-text="zip || 'Click to choose an addon .zip'"></span>
                <input type="file" name="zip" accept=".zip" required class="hidden" @change="zip = $event.target.files[0]?.name || ''">
            </label>
        </div>

        {{-- Listing details --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4 space-y-4">
            <h2 class="text-sm font-semibold text-gray-800">Listing</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Category</label>
                    <select name="category_id" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">— none —</option>
                        @foreach ($categories as $c)
                            <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Tagline <span class="text-gray-400">(short)</span></label>
                    <input type="text" name="tagline" maxlength="160" value="{{ old('tagline') }}" placeholder="One line about the addon"
                           class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Description <span class="text-gray-400">(optional — defaults to addon.json)</span></label>
                <textarea name="description" rows="4" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500" placeholder="What does this addon do? Markdown-ish plain text is fine.">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Icon <span class="text-gray-400">(square image)</span></label>
                    <input type="file" name="icon" accept="image/*" class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Screenshots <span class="text-gray-400">(multiple)</span></label>
                    <input type="file" name="screenshots[]" accept="image/*" multiple @change="shots = $event.target.files.length"
                           class="block w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100">
                    <p class="text-[11px] text-gray-400 mt-1" x-show="shots" x-cloak><span x-text="shots"></span> selected</p>
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <h2 class="text-sm font-semibold text-gray-800 mb-3">Pricing</h2>
            <div class="flex gap-2 mb-3">
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="is_paid" value="0" class="peer sr-only" x-model="paid" :value="false" @click="paid=false" {{ old('is_paid') ? '' : 'checked' }}>
                    <div class="border rounded-lg px-4 py-3 text-sm text-center" :class="!paid ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'"><i class="fas fa-gift mr-1"></i> Free</div>
                </label>
                <label class="flex-1 cursor-pointer">
                    <input type="radio" name="is_paid" value="1" class="peer sr-only" @click="paid=true" {{ old('is_paid') ? 'checked' : '' }}>
                    <div class="border rounded-lg px-4 py-3 text-sm text-center" :class="paid ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' : 'border-gray-200 text-gray-600'"><i class="fas fa-tag mr-1"></i> Paid</div>
                </label>
            </div>
            <div x-show="paid" x-cloak class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Price</label>
                    <input type="number" name="price" step="0.01" min="0" value="{{ old('price', 0) }}" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Currency</label>
                    <input type="text" name="currency" maxlength="10" value="{{ old('currency', 'USD') }}" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-upload"></i> Create draft</button>
            <a href="{{ route('admin.addons.index') }}" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
        </div>
    </form>
</x-app-layout>
