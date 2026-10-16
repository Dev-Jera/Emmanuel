<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = ['key', 'value'];

    public static function value(string $key, string $default = ''): string
    {
        return (string) Cache::remember('site-setting:'.$key, 300, fn () => static::query()->whereKey($key)->value('value') ?? $default);
    }
}
