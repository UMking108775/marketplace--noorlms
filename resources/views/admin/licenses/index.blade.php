<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h1 class="font-semibold text-gray-800 leading-tight">Licenses</h1>
                <p class="text-xs text-gray-500">Keys for paid addons — each can activate on up to its limit of domains</p>
            </div>
            @if ($paidAddons->isNotEmpty())
                <button type="button" onclick="document.getElementById('newLicense').classList.toggle('hidden')" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-plus"></i> New license</button>
            @endif
        </div>
    </x-slot>

    {{-- Create --}}
    @if ($paidAddons->isNotEmpty())
        <div id="newLicense" class="hidden bg-white rounded-xl border border-gray-200 p-5 mb-4">
            <form method="POST" action="{{ route('admin.licenses.store') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Addon</label>
                    <select name="addon_id" required class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500">
                        @foreach ($paidAddons as $a)<option value="{{ $a->id }}">{{ $a->name }} ({{ number_format($a->price, 2) }} {{ $a->currency }})</option>@endforeach
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Customer name</label><input type="text" name="customer_name" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Customer email</label><input type="email" name="customer_email" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Activation limit</label><input type="number" name="activation_limit" value="1" min="1" required class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div><label class="block text-xs font-medium text-gray-600 mb-1">Expires <span class="text-gray-400">(optional)</span></label><input type="date" name="expires_at" class="w-full text-sm rounded-lg border-gray-200 focus:border-indigo-500 focus:ring-indigo-500"></div>
                <div><button type="submit" class="w-full px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700"><i class="fas fa-key mr-1"></i> Generate key</button></div>
            </form>
        </div>
    @else
        <div class="bg-amber-50 border border-amber-200 rounded-lg px-4 py-3 text-sm text-amber-700 mb-4"><i class="fas fa-circle-info mr-1"></i> Mark an addon as <strong>paid</strong> first, then you can issue licenses for it.</div>
    @endif

    {{-- Filters --}}
    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm mb-4">
        @foreach (['' => 'All', 'active' => 'Active', 'suspended' => 'Suspended'] as $k => $l)
            @php $active = ($filters['status'] ?? '') === $k; $c = $k === '' ? $counts['all'] : ($counts[$k] ?? 0); @endphp
            <a href="{{ route('admin.licenses.index', array_filter(['status' => $k])) }}" class="px-3 py-1.5 rounded-md font-medium {{ $active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ $l }} <span class="opacity-70">{{ $c }}</span></a>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] text-gray-400 border-b border-gray-100">
                    <th class="font-medium px-4 py-2.5">Key</th>
                    <th class="font-medium px-3 py-2.5">Addon</th>
                    <th class="font-medium px-3 py-2.5 hidden md:table-cell">Customer</th>
                    <th class="font-medium px-3 py-2.5">Status</th>
                    <th class="font-medium px-3 py-2.5 text-center">Activations</th>
                    <th class="font-medium px-3 py-2.5 hidden sm:table-cell">Expires</th>
                    <th class="font-medium px-4 py-2.5 text-right">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($licenses as $license)
                        <tr x-data="{ edit: false, acts: false }">
                            <td class="px-4 py-2.5">
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $license->license_key }}'); this.querySelector('i').className='fas fa-check text-emerald-500'" class="font-mono text-xs text-gray-700 inline-flex items-center gap-1.5 hover:text-indigo-600" title="Copy">{{ $license->license_key }} <i class="far fa-copy text-gray-300"></i></button>
                            </td>
                            <td class="px-3 py-2.5 text-xs text-gray-700">{{ $license->addon?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 hidden md:table-cell"><p class="text-xs text-gray-700">{{ $license->customer_name ?: '—' }}</p><p class="text-[11px] text-gray-400">{{ $license->customer_email }}</p></td>
                            <td class="px-3 py-2.5">
                                @php $tones = ['active'=>'bg-emerald-100 text-emerald-700','suspended'=>'bg-amber-100 text-amber-700','expired'=>'bg-red-100 text-red-700']; @endphp
                                <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full {{ $tones[$license->status] ?? 'bg-gray-100 text-gray-600' }}">{{ ucfirst($license->status) }}</span>
                            </td>
                            <td class="px-3 py-2.5 text-center">
                                <button type="button" @click="acts = !acts" class="text-xs font-semibold text-gray-700 hover:text-indigo-600">{{ $license->activations->count() }}/{{ $license->activation_limit }}</button>
                            </td>
                            <td class="px-3 py-2.5 hidden sm:table-cell text-xs text-gray-500">{{ $license->expires_at?->format('d M Y') ?? 'never' }}</td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <button type="button" @click="edit = !edit" class="text-gray-400 hover:text-indigo-600 mr-2" title="Edit"><i class="fas fa-pen text-xs"></i></button>
                                <form method="POST" action="{{ route('admin.licenses.destroy', $license) }}" class="inline" onsubmit="return confirm('Delete this license?')">@csrf @method('DELETE')<button class="text-gray-400 hover:text-red-600" title="Delete"><i class="fas fa-trash text-xs"></i></button></form>
                            </td>
                        </tr>
                        {{-- edit row --}}
                        <tr x-show="edit" x-cloak><td colspan="7" class="px-4 py-3 bg-gray-50">
                            <form method="POST" action="{{ route('admin.licenses.update', $license) }}" class="grid grid-cols-2 sm:grid-cols-4 gap-3 items-end">
                                @csrf @method('PUT')
                                <input type="hidden" name="customer_name" value="{{ $license->customer_name }}"><input type="hidden" name="customer_email" value="{{ $license->customer_email }}">
                                <div><label class="block text-[11px] text-gray-500 mb-1">Status</label><select name="status" class="w-full text-xs rounded-lg border-gray-200">@foreach(['active','suspended','expired'] as $s)<option value="{{ $s }}" @selected($license->status===$s)>{{ ucfirst($s) }}</option>@endforeach</select></div>
                                <div><label class="block text-[11px] text-gray-500 mb-1">Limit</label><input type="number" name="activation_limit" value="{{ $license->activation_limit }}" min="1" class="w-full text-xs rounded-lg border-gray-200"></div>
                                <div><label class="block text-[11px] text-gray-500 mb-1">Expires</label><input type="date" name="expires_at" value="{{ $license->expires_at?->format('Y-m-d') }}" class="w-full text-xs rounded-lg border-gray-200"></div>
                                <div><button class="w-full px-3 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold">Save</button></div>
                            </form>
                        </td></tr>
                        {{-- activations row --}}
                        <tr x-show="acts" x-cloak><td colspan="7" class="px-4 py-3 bg-gray-50">
                            @if ($license->activations->isEmpty())
                                <p class="text-xs text-gray-400">No activations yet — the key isn't bound to any domain.</p>
                            @else
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($license->activations as $act)
                                        <span class="inline-flex items-center gap-2 text-xs bg-white border border-gray-200 rounded-lg px-2.5 py-1">
                                            <i class="fas fa-globe text-gray-300"></i> {{ $act->domain }}
                                            <form method="POST" action="{{ route('admin.licenses.deactivate', [$license, $act]) }}" class="inline" onsubmit="return confirm('Free up this activation?')">@csrf @method('DELETE')<button class="text-gray-300 hover:text-red-500"><i class="fas fa-xmark"></i></button></form>
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td></tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-gray-400"><i class="fas fa-key text-2xl mb-2 block text-gray-300"></i>No licenses yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($licenses->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $licenses->links() }}</div>@endif
    </div>
</x-app-layout>
