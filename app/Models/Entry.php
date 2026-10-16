<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Entry extends Model
{
    protected $fillable = ['type', 'slug', 'title', 'summary', 'body', 'date', 'image', 'gallery', 'video_url', 'audio_url', 'published'];

    protected function casts(): array
    {
        return ['date' => 'date', 'gallery' => 'array', 'published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DevotionalComment::class)->latest();
    }
}
