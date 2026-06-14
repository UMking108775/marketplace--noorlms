<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

require __DIR__.'/auth.php';
