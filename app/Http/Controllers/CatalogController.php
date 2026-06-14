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
        $addons = Addon::published()->with('category')
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->string('q') . '%')->orWhere('tagline', 'like', '%' . $request->string('q') . '%')))
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->string('type')->value() === 'free', fn ($q) => $q->where('is_paid', false))
            ->when($request->string('type')->value() === 'paid', fn ($q) => $q->where('is_paid', true))
            ->orderByDesc('is_featured')->latest('published_at')
            ->paginate(12)->withQueryString();

        return view('catalog.index', [
            'addons'     => $addons,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function category(Category $category)
    {
        return redirect()->route('catalog.index', ['category' => $category->slug]);
    }

    public function show(Addon $addon)
    {
        abort_unless($addon->status === 'published', 404);
        $addon->load(['category', 'screenshots', 'versions']);

        return view('catalog.show', compact('addon'));
    }
}
