<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    protected $fillable = ['type', 'slug', 'title', 'summary', 'body', 'date', 'image', 'published'];

    protected function casts(): array
    {
        return ['date' => 'date', 'published' => 'boolean'];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }
}
