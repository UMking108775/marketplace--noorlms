<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Addon extends Model
{
    protected $guarded = [];

    protected $casts = [
        'is_paid' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'rating_avg' => 'decimal:2',
        'published_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function versions()
    {
        return $this->hasMany(AddonVersion::class)->orderByDesc('id');
    }

    public function screenshots()
    {
        return $this->hasMany(AddonScreenshot::class)->orderBy('sort_order');
    }

    public function licenses()
    {
        return $this->hasMany(License::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function isFree(): bool
    {
        return ! $this->is_paid || (float) $this->price <= 0;
    }

    public function getIconUrlAttribute(): ?string
    {
        return $this->icon_path
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($this->icon_path)
            : null;
    }
}
