<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Menu extends Model
{
    protected $fillable = ['name', 'slug', 'location', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Menu $menu) {
            if (empty($menu->slug)) {
                $menu->slug = Str::slug($menu->name);
            }
        });

        static::saved(fn () => Cache::flush());
        static::deleted(fn () => Cache::flush());
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class)->whereNull('parent_id')->orderBy('sort_order');
    }

    public function allItems(): HasMany
    {
        return $this->hasMany(MenuItem::class)->orderBy('sort_order');
    }

    public static function byLocation(string $location): ?self
    {
        return Cache::rememberForever("menu_location_{$location}", function () use ($location) {
            return static::with('items.children')->where('location', $location)->where('is_active', true)->first();
        });
    }
}
