<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class MenuItem extends Model
{
    protected $fillable = ['menu_id', 'parent_id', 'label', 'url', 'target', 'icon', 'sort_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        // Cuando se modifica/borra cualquier item, invalidar la caché del menú padre.
        // Sin esto, Menu::byLocation() cachea forever y los cambios no se reflejan.
        $bust = function (MenuItem $item) {
            $location = $item->menu?->location;
            if ($location) {
                Cache::forget("menu_location_{$location}");
            }
            // Borrar todas las ubicaciones conocidas por si acaso.
            foreach (['header', 'footer', 'footer_secondary'] as $loc) {
                Cache::forget("menu_location_{$loc}");
            }
        };
        static::saved($bust);
        static::deleted($bust);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }
}
