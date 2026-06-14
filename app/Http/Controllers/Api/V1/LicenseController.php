<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\License;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    /** POST /api/v1/licenses/validate — does this key entitle the caller to the addon? */
    public function validateKey(Request $request)
    {
        $data = $request->validate([
            'license_key' => 'required|string',
            'domain'      => 'nullable|string',
            'addon'       => 'nullable|string', // package slug, optional cross-check
        ]);

        $license = License::with('addon')->where('license_key', $data['license_key'])->first();

        if (! $license) {
            return response()->json(['valid' => false, 'reason' => 'not_found', 'message' => 'License key not found.'], 404);
        }

        if (! $license->isValid()) {
            return response()->json(['valid' => false, 'reason' => $license->status, 'message' => "License is {$license->status}."]);
        }

        if (! empty($data['addon']) && $license->addon
            && $license->addon->slug !== $data['addon'] && $license->addon->package_name !== $data['addon']) {
            return response()->json(['valid' => false, 'reason' => 'addon_mismatch', 'message' => 'This license is for a different addon.']);
        }

        return response()->json([
            'valid'       => true,
            'addon'       => $license->addon?->slug,
            'package'     => $license->addon?->package_name,
            'expires_at'  => $license->expires_at?->toIso8601String(),
            'activations' => ['used' => $license->activations_used, 'limit' => $license->activation_limit],
        ]);
    }
}
