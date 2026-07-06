<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class FaqCategory extends Model
{
    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class, 'category_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }
}
