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

    public static function generateKey(): string
    {
        return strtoupper(implode('-', str_split(bin2hex(random_bytes(10)), 5)));
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && (is_null($this->expires_at) || $this->expires_at->isFuture());
    }
}
