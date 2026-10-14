<?php

namespace App\Services\MarzPay;

use App\Models\GivingOrder;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class HttpMarzPayGateway implements MarzPayGateway
{
    public function isMock(): bool
    {
        return false;
    }

    public function collect(GivingOrder $order): array
    {
        $response = $this->client()->post('/collect-money', [
            'amount' => $order->amount,
            'phone_number' => $order->phone,
            'reference' => $order->reference,
            'country' => config('services.marzpay.country', 'UG'),
            'description' => $this->description($order),
            'callback_url' => $this->callbackUrl(),
            'metadata' => [
                ['donationId' => (string) $order->id],
                ['donorName' => $order->name, 'isPII' => true],
            ],
        ]);

        $body = $response->json() ?? [];

        if ($response->failed() || ($body['status'] ?? null) === 'error') {
            throw new MarzPayException(
                $body['message'] ?? 'MarzPay rejected the collection request.',
                ['status' => $response->status(), 'body' => $body],
            );
        }

        $transaction = $body['data']['transaction'] ?? [];
        $collection = $body['data']['collection'] ?? [];

        return [
            'transaction_uuid' => $transaction['uuid'] ?? null,
            'provider' => $collection['provider'] ?? null,
            'status' => $transaction['status'] ?? 'processing',
            'raw' => $body,
        ];
    }

    public function status(GivingOrder $order): array
    {
        $uuid = $order->transaction_uuid ?: $order->reference;
        $response = $this->client()->get('/collect-money/'.$uuid);
        $body = $response->json() ?? [];

        if ($response->failed()) {
            throw new MarzPayException(
                $body['message'] ?? 'Could not read the collection status.',
                ['status' => $response->status(), 'body' => $body],
            );
        }

        // Collect/transaction detail is callback-shaped (event_type + transaction),
        // but tolerate the create-style status/data envelope as well.
        $transaction = $body['transaction'] ?? $body['data']['transaction'] ?? $body['data'] ?? [];
        $collection = $body['collection'] ?? $body['data']['collection'] ?? [];

        return [
            'status' => $transaction['status'] ?? 'processing',
            'provider' => $collection['provider'] ?? $transaction['provider'] ?? null,
            'provider_transaction_id' => $collection['provider_transaction_id'] ?? null,
            'amount' => $this->raw($transaction['amount'] ?? $collection['amount'] ?? null),
            'charge' => $this->raw($transaction['charge'] ?? $collection['charge'] ?? null),
            'net_amount' => $this->raw($transaction['net_amount'] ?? $collection['net_amount'] ?? null),
            'raw' => $body,
        ];
    }

    public function callbackUrl(): string
    {
        return (string) (config('services.marzpay.callback_url') ?: route('giving.webhook'));
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.marzpay.base_url'), '/'))
            ->withBasicAuth((string) config('services.marzpay.key'), (string) config('services.marzpay.secret'))
            ->acceptJson()
            ->timeout(30);
    }

    private function description(GivingOrder $order): string
    {
        $purpose = $order->purpose ? ' - '.$order->purpose : '';

        return mb_substr('Church giving'.$purpose, 0, 255);
    }

    private function raw(mixed $money): ?int
    {
        if (is_array($money)) {
            return isset($money['raw']) ? (int) $money['raw'] : null;
        }

        return is_numeric($money) ? (int) $money : null;
    }
}
