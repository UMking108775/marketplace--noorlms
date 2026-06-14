<x-app-layout>
    <x-slot name="header">
        <div>
            <h1 class="font-semibold text-gray-800 leading-tight">Reviews</h1>
            <p class="text-xs text-gray-500">Moderate addon reviews — hidden reviews don't count toward ratings</p>
        </div>
    </x-slot>

    <div class="inline-flex rounded-lg border border-gray-200 bg-white p-1 text-sm mb-4">
        @foreach (['' => 'All', '1' => 'Approved', '0' => 'Hidden'] as $k => $l)
            @php $active = (string) ($filters['approved'] ?? '') === $k; @endphp
            <a href="{{ route('admin.reviews.index', array_filter(['approved' => $k], fn ($v) => $v !== '')) }}" class="px-3 py-1.5 rounded-md font-medium {{ $active ? 'bg-indigo-600 text-white' : 'text-gray-600 hover:bg-gray-100' }}">{{ $l }}</a>
        @endforeach
        <span class="px-3 py-1.5 text-gray-400">{{ $counts['all'] }} total · {{ $counts['pending'] }} hidden</span>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead><tr class="text-left text-[11px] text-gray-400 border-b border-gray-100">
                    <th class="font-medium px-4 py-2.5">Addon</th>
                    <th class="font-medium px-3 py-2.5">Reviewer</th>
                    <th class="font-medium px-3 py-2.5">Rating</th>
                    <th class="font-medium px-3 py-2.5">Comment</th>
                    <th class="font-medium px-3 py-2.5">Status</th>
                    <th class="font-medium px-4 py-2.5 text-right">Actions</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($reviews as $review)
                        <tr>
                            <td class="px-4 py-2.5 text-xs font-medium text-gray-700">{{ $review->addon?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5"><p class="text-xs text-gray-700">{{ $review->reviewer_name }}</p><p class="text-[11px] text-gray-400">{{ $review->created_at?->format('d M Y') }}</p></td>
                            <td class="px-3 py-2.5 whitespace-nowrap text-amber-400 text-xs">
                                @for ($i = 1; $i <= 5; $i++)<i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-200' }}"></i>@endfor
                            </td>
                            <td class="px-3 py-2.5 text-xs text-gray-600 max-w-[280px]"><p class="line-clamp-2">{{ $review->comment ?: '—' }}</p></td>
                            <td class="px-3 py-2.5">
                                @if ($review->is_approved)
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700">Approved</span>
                                @else
                                    <span class="text-[11px] font-semibold px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Hidden</span>
                                @endif
                            </td>
                            <td class="px-4 py-2.5 text-right whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.reviews.update', $review) }}" class="inline">@csrf @method('PUT')
                                    <button class="text-xs font-medium {{ $review->is_approved ? 'text-gray-500 hover:text-amber-600' : 'text-emerald-600 hover:text-emerald-700' }} mr-3">{{ $review->is_approved ? 'Hide' : 'Approve' }}</button>
                                </form>
                                <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" class="inline" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')
                                    <button class="text-gray-400 hover:text-red-600" title="Delete"><i class="fas fa-trash text-xs"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-12 text-center text-sm text-gray-400"><i class="fas fa-star text-2xl mb-2 block text-gray-300"></i>No reviews yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($reviews->hasPages())<div class="px-4 py-3 border-t border-gray-100">{{ $reviews->links() }}</div>@endif
    </div>
</x-app-layout>
