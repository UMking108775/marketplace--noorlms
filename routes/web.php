<?php

use App\Http\Controllers\Admin\AddonController;
use App\Http\Controllers\Admin\LicenseController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// ── Public catalog (Play-Store-style) ──
Route::get('/', [CatalogController::class, 'home'])->name('catalog.home');
Route::get('/addons', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/addons/{addon:slug}', [CatalogController::class, 'show'])->name('catalog.show');
Route::post('/addons/{addon:slug}/reviews', [CatalogController::class, 'storeReview'])->middleware('throttle:5,1')->name('catalog.reviews.store');
Route::get('/categories/{category:slug}', [CatalogController::class, 'category'])->name('catalog.category');

Route::get('/dashboard', function () {
    return view('dashboard', ['stats' => [
        'addons'     => \App\Models\Addon::count(),
        'published'  => \App\Models\Addon::where('status', 'published')->count(),
        'paid'       => \App\Models\Addon::where('is_paid', true)->count(),
        'categories' => \App\Models\Category::count(),
        'licenses'   => \App\Models\License::where('status', 'active')->count(),
        'sites'      => \App\Models\Site::count(),
        'reviews'    => \App\Models\Review::count(),
    ]]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Admin: addon management ──
Route::middleware(['auth', 'verified'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('addons', [AddonController::class, 'index'])->name('addons.index');
    Route::get('addons/create', [AddonController::class, 'create'])->name('addons.create');
    Route::post('addons', [AddonController::class, 'store'])->name('addons.store');
    Route::get('addons/{addon}/edit', [AddonController::class, 'edit'])->name('addons.edit');
    Route::put('addons/{addon}', [AddonController::class, 'update'])->name('addons.update');
    Route::delete('addons/{addon}', [AddonController::class, 'destroy'])->name('addons.destroy');
    Route::post('addons/{addon}/publish', [AddonController::class, 'publish'])->name('addons.publish');

    Route::post('addons/{addon}/versions', [AddonController::class, 'addVersion'])->name('addons.versions.store');
    Route::delete('addons/{addon}/versions/{version}', [AddonController::class, 'deleteVersion'])->name('addons.versions.destroy');

    Route::post('addons/{addon}/screenshots', [AddonController::class, 'addScreenshots'])->name('addons.screenshots.store');
    Route::delete('addons/{addon}/screenshots/{screenshot}', [AddonController::class, 'deleteScreenshot'])->name('addons.screenshots.destroy');

    // Licenses (paid addons)
    Route::get('licenses', [LicenseController::class, 'index'])->name('licenses.index');
    Route::post('licenses', [LicenseController::class, 'store'])->name('licenses.store');
    Route::put('licenses/{license}', [LicenseController::class, 'update'])->name('licenses.update');
    Route::delete('licenses/{license}', [LicenseController::class, 'destroy'])->name('licenses.destroy');
    Route::delete('licenses/{license}/activations/{activation}', [LicenseController::class, 'deactivate'])->name('licenses.deactivate');

    // Reviews moderation
    Route::get('reviews', [ReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{review}', [ReviewController::class, 'update'])->name('reviews.update');
    Route::delete('reviews/{review}', [ReviewController::class, 'destroy'])->name('reviews.destroy');
});

require __DIR__.'/auth.php';
