<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddonScreenshot extends Model
{
    protected $guarded = [];

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }

    public function getUrlAttribute(): string
    {
        return \Illuminate\Support\Facades\Storage::disk('public')->url($this->path);
    }
}
