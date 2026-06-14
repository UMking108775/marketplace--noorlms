<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\Category;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function home()
    {
        return view('catalog.home', [
            'count'      => Addon::published()->count(),
            'categories' => Category::where('is_active', true)->withCount(['addons' => fn ($q) => $q->published()])->orderBy('sort_order')->get(),
            'featured'   => Addon::published()->with('category')->where('is_featured', true)->latest('published_at')->take(6)->get(),
            'recent'     => Addon::published()->with('category')->latest('published_at')->take(8)->get(),
        ]);
    }

    public function index(Request $request)
    {
        $query = Addon::published()->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->string('q') . '%')->orWhere('tagline', 'like', '%' . $request->string('q') . '%')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->string('type')->value() === 'free', fn ($q) => $q->where('is_paid', false))
            ->when($request->string('type')->value() === 'paid', fn ($q) => $q->where('is_paid', true));

        $this->applySort($query, $request->string('sort')->value());

        return view('catalog.index', [
            'addons'     => $query->paginate(12)->withQueryString(),
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'sort'       => $request->string('sort')->value(),
        ]);
    }

    protected function applySort($query, string $sort): void
    {
        match ($sort) {
            'popular'    => $query->orderByDesc('downloads_count'),
            'rating'     => $query->orderByDesc('rating_avg')->orderByDesc('rating_count'),
            'price_asc'  => $query->orderBy('is_paid')->orderBy('price'),
            'price_desc' => $query->orderByDesc('price'),
            default      => $query->orderByDesc('is_featured')->latest('published_at'),
        };
    }

    public function category(Category $category)
    {
        return redirect()->route('catalog.index', ['category' => $category->slug]);
    }

    public function show(Addon $addon)
    {
        abort_unless($addon->status === 'published', 404);
        $addon->load(['category', 'screenshots', 'versions', 'reviews' => fn ($q) => $q->where('is_approved', true)->latest()]);

        return view('catalog.show', compact('addon'));
    }

    public function storeReview(Request $request, Addon $addon)
    {
        abort_unless($addon->status === 'published', 404);

        $data = $request->validate([
            'reviewer_name' => 'required|string|max:80',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'nullable|string|max:1500',
        ]);

        $addon->reviews()->create($data + ['is_approved' => true]);
        $addon->recomputeRating();

        return back()->with('reviewed', true);
    }
}
