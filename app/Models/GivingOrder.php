<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GivingOrder extends Model
{
    protected $fillable = [
        'reference', 'transaction_uuid', 'provider_transaction_id', 'provider',
        'name', 'email', 'phone', 'amount', 'charge', 'net_amount', 'currency',
        'purpose', 'message', 'status', 'raw', 'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'charge' => 'integer',
            'net_amount' => 'integer',
            'raw' => 'array',
            'paid_at' => 'datetime',
        ];
    }

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function isFinal(): bool
    {
        return in_array($this->status, ['completed', 'failed', 'cancelled'], true);
    }

    public function providerLabel(): string
    {
        return match ($this->provider) {
            'mtn' => 'MTN MoMo',
            'airtel' => 'Airtel Money',
            default => $this->provider ? ucfirst($this->provider) : '—',
        };
    }
}
