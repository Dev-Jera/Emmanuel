<?php

namespace App\Services\MarzPay;

use App\Models\GivingOrder;
use Illuminate\Support\Str;

class MockMarzPayGateway implements MarzPayGateway
{
    public function isMock(): bool
    {
        return true;
    }

    public function collect(GivingOrder $order): array
    {
        $raw = [
            'status' => 'success',
            'message' => 'Collection initiated successfully (Sandbox Mode).',
            'data' => [
                'transaction' => [
                    'uuid' => (string) Str::uuid(),
                    'reference' => $order->reference,
                    'status' => 'processing',
                    'provider_reference' => null,
                ],
                'collection' => [
                    'amount' => [
                        'formatted' => number_format($order->amount),
                        'raw' => $order->amount,
                        'currency' => $order->currency,
                    ],
                    'provider' => $order->provider,
                    'phone_number' => $order->phone,
                    'mode' => 'sandbox',
                ],
                'metadata' => [
                    'response_timestamp' => now()->toDateTimeString(),
                    'sandbox_mode' => true,
                ],
            ],
        ];

        return [
            'transaction_uuid' => $raw['data']['transaction']['uuid'],
            'provider' => $order->provider,
            'status' => 'processing',
            'raw' => $raw,
        ];
    }

    public function status(GivingOrder $order): array
    {
        return [
            'status' => $order->status,
            'provider' => $order->provider,
            'provider_transaction_id' => $order->provider_transaction_id,
            'amount' => $order->amount,
            'charge' => $order->charge,
            'net_amount' => $order->net_amount,
            'raw' => $order->raw ?? [],
        ];
    }

    public function callbackUrl(): string
    {
        return (string) (config('services.marzpay.callback_url') ?: route('giving.webhook'));
    }
}
