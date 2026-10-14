<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevotionalComment extends Model
{
    protected $fillable = ['name', 'body'];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(Entry::class);
    }
}
