<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AddonScreenshot;
use App\Models\AddonVersion;
use App\Models\Category;
use App\Services\AddonPackage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AddonController extends Controller
{
    public function index(Request $request)
    {
        $addons = Addon::with('category')->withCount('versions')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->string('q') . '%'))
            ->latest()->paginate(12)->withQueryString();

        return view('admin.addons.index', [
            'addons' => $addons,
            'counts' => [
                'all'       => Addon::count(),
                'published' => Addon::where('status', 'published')->count(),
                'draft'     => Addon::where('status', 'draft')->count(),
            ],
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function create()
    {
        return view('admin.addons.create', ['categories' => Category::orderBy('sort_order')->get()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'zip'            => 'required|file|max:51200',
            'category_id'    => 'nullable|exists:categories,id',
            'tagline'        => 'nullable|string|max:160',
            'description'    => 'nullable|string',
            'icon'           => 'nullable|image|max:2048',
            'screenshots.*'  => 'nullable|image|max:4096',
            'is_paid'        => 'nullable|boolean',
            'price'          => 'nullable|numeric|min:0',
            'currency'       => 'nullable|string|max:10',
            'changelog'      => 'nullable|string',
        ]);

        if (! Str::endsWith(strtolower($request->file('zip')->getClientOriginalName()), '.zip')) {
            return back()->withInput()->with('error', 'The package must be a .zip file.');
        }

        try {
            $manifest = AddonPackage::readManifest($request->file('zip')->getRealPath());
        } catch (\Throwable $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        if (Addon::where('vendor', $manifest['vendor'])->where('package_name', $manifest['name'])->exists()) {
            return back()->withInput()->with('error', "An addon \"{$manifest['vendor']}/{$manifest['name']}\" is already listed. Add a new version to it instead.");
        }

        $name = AddonPackage::displayName($manifest);
        $version = AddonPackage::version($manifest);
        $paid = $request->boolean('is_paid');

        $addon = Addon::create([
            'user_id'         => auth()->id(),
            'category_id'     => $data['category_id'] ?? null,
            'name'            => $name,
            'slug'            => $this->uniqueSlug($name),
            'package_name'    => $manifest['name'],
            'vendor'          => $manifest['vendor'],
            'tagline'         => $data['tagline'] ?? null,
            'description'     => $data['description'] ?? ($manifest['description'] ?? null),
            'icon_path'       => $request->hasFile('icon') ? $request->file('icon')->store('addons/icons', 'public') : null,
            'is_paid'         => $paid,
            'price'           => $paid ? ($data['price'] ?? 0) : 0,
            'currency'        => $data['currency'] ?? 'USD',
            'status'          => 'draft',
            'latest_version'  => $version,
            'min_lms_version' => $manifest['minimum_lms_version'] ?? null,
        ]);

        $this->storeVersion($request->file('zip'), $addon, $version, $data['changelog'] ?? null, $manifest);
        $this->storeScreenshots($request, $addon);

        return redirect()->route('admin.addons.edit', $addon)
            ->with('success', "“{$name}” created as a draft. Add screenshots and publish when ready.");
    }

    public function edit(Addon $addon)
    {
        $addon->load(['versions', 'screenshots', 'category']);

        return view('admin.addons.edit', [
            'addon'      => $addon,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Addon $addon)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'tagline'     => 'nullable|string|max:160',
            'description' => 'nullable|string',
            'icon'        => 'nullable|image|max:2048',
            'is_paid'     => 'nullable|boolean',
            'price'       => 'nullable|numeric|min:0',
            'currency'    => 'nullable|string|max:10',
        ]);

        $paid = $request->boolean('is_paid');
        $payload = [
            'category_id' => $data['category_id'] ?? null,
            'tagline'     => $data['tagline'] ?? null,
            'description' => $data['description'] ?? null,
            'is_paid'     => $paid,
            'price'       => $paid ? ($data['price'] ?? 0) : 0,
            'currency'    => $data['currency'] ?? 'USD',
        ];

        if ($request->hasFile('icon')) {
            $this->deleteFile($addon->icon_path);
            $payload['icon_path'] = $request->file('icon')->store('addons/icons', 'public');
        }

        $addon->update($payload);

        return back()->with('success', 'Addon details saved.');
    }

    public function publish(Addon $addon)
    {
        if ($addon->status !== 'published') {
            $addon->update(['status' => 'published', 'published_at' => $addon->published_at ?? now()]);
            $msg = 'Addon published — it is now visible to connected LMS installs.';
        } else {
            $addon->update(['status' => 'draft']);
            $msg = 'Addon unpublished (back to draft).';
        }

        return back()->with('success', $msg);
    }

    public function destroy(Addon $addon)
    {
        foreach ($addon->versions as $v) {
            $this->deleteFile($v->zip_path);
        }
        foreach ($addon->screenshots as $s) {
            $this->deleteFile($s->path);
        }
        $this->deleteFile($addon->icon_path);
        $addon->delete();

        return redirect()->route('admin.addons.index')->with('success', 'Addon deleted.');
    }

    // ── versions ──
    public function addVersion(Request $request, Addon $addon)
    {
        $data = $request->validate([
            'zip'       => 'required|file|max:51200',
            'changelog' => 'nullable|string',
        ]);

        try {
            $manifest = AddonPackage::readManifest($request->file('zip')->getRealPath());
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        if ($manifest['name'] !== $addon->package_name || $manifest['vendor'] !== $addon->vendor) {
            return back()->with('error', "That package is for \"{$manifest['vendor']}/{$manifest['name']}\", not this addon.");
        }

        $version = AddonPackage::version($manifest);
        if ($addon->versions()->where('version', $version)->exists()) {
            return back()->with('error', "Version {$version} already exists for this addon.");
        }

        $addon->versions()->update(['is_latest' => false]);
        $this->storeVersion($request->file('zip'), $addon, $version, $data['changelog'] ?? null, $manifest);
        $addon->update(['latest_version' => $version, 'min_lms_version' => $manifest['minimum_lms_version'] ?? $addon->min_lms_version]);

        return back()->with('success', "Version {$version} uploaded.");
    }

    public function deleteVersion(Addon $addon, AddonVersion $version)
    {
        abort_unless($version->addon_id === $addon->id, 404);

        if ($addon->versions()->count() <= 1) {
            return back()->with('error', "Can't delete the only version. Delete the addon instead.");
        }

        $this->deleteFile($version->zip_path);
        $wasLatest = $version->is_latest;
        $version->delete();

        if ($wasLatest) {
            $newest = $addon->versions()->orderByDesc('id')->first();
            if ($newest) {
                $newest->update(['is_latest' => true]);
                $addon->update(['latest_version' => $newest->version]);
            }
        }

        return back()->with('success', 'Version deleted.');
    }

    // ── screenshots ──
    public function addScreenshots(Request $request, Addon $addon)
    {
        $request->validate(['screenshots.*' => 'required|image|max:4096']);
        $this->storeScreenshots($request, $addon);

        return back()->with('success', 'Screenshots added.');
    }

    public function deleteScreenshot(Addon $addon, AddonScreenshot $screenshot)
    {
        abort_unless($screenshot->addon_id === $addon->id, 404);
        $this->deleteFile($screenshot->path);
        $screenshot->delete();

        return back()->with('success', 'Screenshot removed.');
    }

    // ── helpers ──
    protected function storeVersion($zip, Addon $addon, string $version, ?string $changelog, array $manifest): AddonVersion
    {
        $filename = Str::slug($addon->slug . '-' . $version) . '-' . Str::random(6) . '.zip';

        return $addon->versions()->create([
            'version'         => $version,
            'changelog'       => $changelog,
            'zip_path'        => $zip->storeAs('addons/packages', $filename, 'public'),
            'min_lms_version' => $manifest['minimum_lms_version'] ?? null,
            'file_size'       => $zip->getSize(),
            'checksum'        => hash_file('sha256', $zip->getRealPath()),
            'is_latest'       => true,
            'released_at'     => now(),
        ]);
    }

    protected function storeScreenshots(Request $request, Addon $addon): void
    {
        if (! $request->hasFile('screenshots')) {
            return;
        }
        $start = (int) $addon->screenshots()->max('sort_order');
        foreach ($request->file('screenshots') as $i => $file) {
            $addon->screenshots()->create([
                'path'       => $file->store('addons/screenshots', 'public'),
                'sort_order' => $start + $i + 1,
            ]);
        }
    }

    protected function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'addon';
        $slug = $base;
        $n = 2;
        while (Addon::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $n++;
        }

        return $slug;
    }

    protected function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
