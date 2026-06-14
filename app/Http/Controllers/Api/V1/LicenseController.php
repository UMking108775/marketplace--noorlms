<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /**
     * POST /api/v1/licenses/validate
     *
     * Accepts both the marketplace's own params and the LMS AddonLicenseValidator
     * contract ({addon, vendor, version, key, domain}). Binds the calling domain
     * against the activation limit.
     */
    public function validateKey(Request $request)
    {
        $key = $request->input('key', $request->input('license_key'));
        if (! $key) {
            return response()->json(['valid' => false, 'reason' => 'missing_key', 'message' => 'A license key is required.'], 422);
        }

        $license = License::with('addon')->where('license_key', $key)->first();
        if (! $license) {
            return response()->json(['valid' => false, 'reason' => 'not_found', 'message' => 'License key not found.'], 404);
        }

        // The LMS sends the addon's package name; cross-check it.
        $addon = $request->input('addon');
        if ($addon && $license->addon && $license->addon->package_name !== $addon && $license->addon->slug !== $addon) {
            return response()->json(['valid' => false, 'reason' => 'addon_mismatch', 'message' => 'This license is for a different addon.']);
        }

        $result = $license->activate($request->input('domain'));
        if (! $result['ok']) {
            return response()->json(['valid' => false, 'reason' => $result['reason'], 'message' => $result['message']]);
        }

        return response()->json([
            'valid'       => true,
            'addon'       => $license->addon?->package_name,
            'expires_at'  => $license->expires_at?->toIso8601String(),
            'activations' => ['used' => $license->activations()->count(), 'limit' => (int) $license->activation_limit],
        ]);
    }
}
