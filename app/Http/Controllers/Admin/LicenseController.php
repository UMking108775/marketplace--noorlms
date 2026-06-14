<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index(Request $request)
    {
        $licenses = License::with(['addon', 'activations'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('addon'), fn ($q) => $q->where('addon_id', $request->integer('addon')))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.licenses.index', [
            'licenses'   => $licenses,
            'paidAddons' => Addon::where('is_paid', true)->orderBy('name')->get(),
            'filters'    => $request->only(['status', 'addon']),
            'counts'     => [
                'all'       => License::count(),
                'active'    => License::where('status', 'active')->count(),
                'suspended' => License::where('status', 'suspended')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'addon_id'         => 'required|exists:addons,id',
            'customer_name'    => 'nullable|string|max:120',
            'customer_email'   => 'nullable|email|max:160',
            'activation_limit' => 'required|integer|min:1|max:1000',
            'expires_at'       => 'nullable|date|after:today',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $data['license_key'] = License::generateKey();
        $data['status'] = 'active';
        $license = License::create($data);

        return back()->with('success', "License {$license->license_key} created.");
    }

    public function update(Request $request, License $license)
    {
        $data = $request->validate([
            'status'           => 'required|in:active,suspended,expired',
            'activation_limit' => 'required|integer|min:1|max:1000',
            'expires_at'       => 'nullable|date',
            'customer_name'    => 'nullable|string|max:120',
            'customer_email'   => 'nullable|email|max:160',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $license->update($data);

        return back()->with('success', 'License updated.');
    }

    public function destroy(License $license)
    {
        $license->delete();

        return back()->with('success', 'License deleted.');
    }

    /** Free up one activation (a bound domain) so it can be reused elsewhere. */
    public function deactivate(License $license, LicenseActivation $activation)
    {
        abort_unless($activation->license_id === $license->id, 404);
        $activation->delete();
        $license->decrement('activations_used');

        return back()->with('success', "Deactivated {$activation->domain}.");
    }
}
