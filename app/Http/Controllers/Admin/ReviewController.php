<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $reviews = Review::with('addon')
            ->when($request->filled('approved'), fn ($q) => $q->where('is_approved', $request->input('approved') === '1'))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'counts'  => [
                'all'     => Review::count(),
                'pending' => Review::where('is_approved', false)->count(),
            ],
            'filters' => $request->only('approved'),
        ]);
    }

    public function update(Review $review)
    {
        $review->update(['is_approved' => ! $review->is_approved]);
        $review->addon?->recomputeRating();

        return back()->with('success', $review->is_approved ? 'Review approved.' : 'Review hidden.');
    }

    public function destroy(Review $review)
    {
        $addon = $review->addon;
        $review->delete();
        $addon?->recomputeRating();

        return back()->with('success', 'Review deleted.');
    }
}
