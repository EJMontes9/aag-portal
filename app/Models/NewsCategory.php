<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsCategory extends Model
{
    protected $fillable = ['name', 'slug', 'color', 'sort_order'];

    protected static function booted(): void
    {
        static::saving(function (self $m) {
            if (empty($m->slug)) {
                $m->slug = Str::slug($m->name);
            }
        });
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class, 'category_id');
    }

    public function publishedCount(): int
    {
        return $this->news()->where('status', 'published')->where('published_at', '<=', now())->count();
    }
}
