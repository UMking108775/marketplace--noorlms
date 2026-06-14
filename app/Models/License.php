<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class License extends Model
{
    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function activations()
    {
        return $this->hasMany(LicenseActivation::class);
    }

    public static function generateKey(): string
    {
        return strtoupper(implode('-', str_split(bin2hex(random_bytes(10)), 5)));
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }

    /**
     * Validate the license and bind/check the calling domain against the
     * activation limit.
     *
     * @return array{ok: bool, reason?: string, message?: string}
     */
    public function activate(?string $domain): array
    {
        if (! $this->isValid()) {
            $reason = ($this->status === 'active') ? 'expired' : $this->status;
            return ['ok' => false, 'reason' => $reason, 'message' => "License is {$reason}."];
        }

        // No domain supplied (e.g. a plain validate ping) — license is valid.
        if (! $domain) {
            return ['ok' => true];
        }

        $domain = strtolower(preg_replace('#^https?://#', '', trim($domain)));
        $domain = explode('/', $domain)[0];

        $existing = $this->activations()->where('domain', $domain)->first();
        if ($existing) {
            $existing->update(['last_seen_at' => now()]);
            return ['ok' => true];
        }

        if ($this->activations()->count() >= (int) $this->activation_limit) {
            return ['ok' => false, 'reason' => 'limit_reached', 'message' => "Activation limit reached ({$this->activation_limit}). Deactivate another site first."];
        }

        $this->activations()->create(['domain' => $domain, 'last_seen_at' => now()]);
        $this->increment('activations_used');

        return ['ok' => true];
    }
}
