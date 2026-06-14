<?php

use App\Http\Controllers\Api\V1\AddonController;
use App\Http\Controllers\Api\V1\LicenseController;
use Illuminate\Support\Facades\Route;

// Public marketplace API consumed by LMS installs.
Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('addons', [AddonController::class, 'index'])->name('addons.index');
    Route::get('categories', [AddonController::class, 'categories'])->name('categories');
    Route::get('addons/{slug}', [AddonController::class, 'show'])->name('addons.show');
    Route::get('addons/{slug}/download', [AddonController::class, 'download'])->name('addons.download');

    Route::post('licenses/validate', [LicenseController::class, 'validateKey'])->name('licenses.validate');
});
