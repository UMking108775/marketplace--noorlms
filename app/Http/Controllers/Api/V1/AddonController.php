<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\Category;
use App\Models\License;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AddonController extends Controller
{
    /** GET /api/v1/addons — published addons, filterable. */
    public function index(Request $request)
    {
        $addons = Addon::published()->with('category')
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->string('category'))))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $request->string('q') . '%')->orWhere('tagline', 'like', '%' . $request->string('q') . '%')))
            ->when($request->string('type')->value() === 'free', fn ($q) => $q->where('is_paid', false))
            ->when($request->string('type')->value() === 'paid', fn ($q) => $q->where('is_paid', true))
            ->orderByDesc('is_featured')->orderByDesc('published_at')
            ->paginate(20)->withQueryString();

        return response()->json([
            'data' => $addons->getCollection()->map(fn ($a) => $this->summary($a)),
            'meta' => [
                'current_page' => $addons->currentPage(),
                'last_page'    => $addons->lastPage(),
                'total'        => $addons->total(),
            ],
        ]);
    }

    /** GET /api/v1/addons/{slug} — full detail + versions + screenshots. */
    public function show(string $slug)
    {
        $addon = Addon::published()->with(['category', 'screenshots', 'versions'])->where('slug', $slug)->firstOrFail();

        return response()->json(['data' => $this->detail($addon)]);
    }

    /** GET /api/v1/addons/{slug}/download — stream the zip (free; paid needs a license). */
    public function download(Request $request, string $slug)
    {
        $addon = Addon::published()->where('slug', $slug)->firstOrFail();

        $version = $request->filled('version')
            ? $addon->versions()->where('version', $request->string('version'))->first()
            : $addon->versions()->where('is_latest', true)->first();

        if (! $version) {
            return response()->json(['error' => 'version_not_found', 'message' => 'No such version.'], 404);
        }

        if ($addon->is_paid) {
            $key = $request->header('X-License-Key') ?: $request->query('license');
            if (! $key) {
                return response()->json(['error' => 'license_required', 'message' => 'This is a paid addon — a license key is required.'], 402);
            }
            $license = License::where('addon_id', $addon->id)->where('license_key', $key)->first();
            if (! $license) {
                return response()->json(['error' => 'invalid_license', 'message' => 'License key not found for this addon.'], 403);
            }
            $activation = $license->activate($request->header('X-Site-Domain') ?: $request->query('domain'));
            if (! $activation['ok']) {
                return response()->json(['error' => $activation['reason'], 'message' => $activation['message']], 403);
            }
        }

        $addon->increment('downloads_count');
        $version->increment('downloads_count');

        return Storage::disk('public')->download($version->zip_path, $addon->slug . '-' . $version->version . '.zip');
    }

    /** GET /api/v1/categories */
    public function categories()
    {
        $cats = Category::where('is_active', true)
            ->withCount(['addons' => fn ($q) => $q->published()])
            ->orderBy('sort_order')->get()
            ->map(fn ($c) => ['name' => $c->name, 'slug' => $c->slug, 'icon' => $c->icon, 'addons_count' => $c->addons_count]);

        return response()->json(['data' => $cats]);
    }

    // ── transformers ──
    private function summary(Addon $a): array
    {
        return [
            'slug'           => $a->slug,
            'name'           => $a->name,
            'vendor'         => $a->vendor,
            'package_name'   => $a->package_name,
            'tagline'        => $a->tagline,
            'icon_url'       => $a->icon_url,
            'category'       => $a->category?->name,
            'is_paid'        => (bool) $a->is_paid,
            'price'          => (float) $a->price,
            'currency'       => $a->currency,
            'latest_version' => $a->latest_version,
            'min_lms_version' => $a->min_lms_version,
            'downloads'      => $a->downloads_count,
            'rating'         => (float) $a->rating_avg,
            'rating_count'   => $a->rating_count,
            'updated_at'     => $a->updated_at?->toIso8601String(),
        ];
    }

    private function detail(Addon $a): array
    {
        return array_merge($this->summary($a), [
            'description' => $a->description,
            'screenshots' => $a->screenshots->map(fn ($s) => $s->url)->values(),
            'versions'    => $a->versions->map(fn ($v) => [
                'version'         => $v->version,
                'changelog'       => $v->changelog,
                'min_lms_version' => $v->min_lms_version,
                'file_size'       => $v->file_size,
                'released_at'     => $v->released_at?->toIso8601String(),
                'is_latest'       => (bool) $v->is_latest,
                'download_url'    => url('/api/v1/addons/' . $a->slug . '/download?version=' . $v->version),
            ])->values(),
        ]);
    }
}
