<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonVersion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_latest' => 'boolean',
        'released_at' => 'datetime',
    ];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
