<?php

namespace App\Http\Controllers;

use App\Models\Entry;
use App\Models\GivingOrder;
use App\Models\SiteSetting;
use App\Services\MarzPay\MarzPayGateway;
use App\Support\UgPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class GivingController extends Controller
{
    private const MIN_AMOUNT = 500;

    private const MAX_AMOUNT = 10000000;

    public function show(): View
    {
        return view('site.giving', [
            'page' => Entry::published()->where('type', 'page')->where('slug', 'giving')->first(),
            'prayer' => SiteSetting::value('giving_prayer'),
            'verse' => SiteSetting::value('giving_verse'),
            'verseReference' => SiteSetting::value('giving_verse_ref'),
            'purposes' => $this->purposes(),
            'minAmount' => self::MIN_AMOUNT,
            'maxAmount' => self::MAX_AMOUNT,
        ]);
    }

    public function initiate(Request $request, MarzPayGateway $gateway): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['required', 'string', 'max:32'],
            'amount' => ['required', 'integer', 'min:'.self::MIN_AMOUNT, 'max:'.self::MAX_AMOUNT],
            'network' => ['nullable', Rule::in(['mtn', 'airtel'])],
            'purpose' => ['nullable', 'string', 'max:120'],
            'message' => ['nullable', 'string', 'max:1000'],
        ]);

        $phone = UgPhone::normalize($validated['phone']);

        if (! $phone || ! UgPhone::isMobile($phone)) {
            return back()
                ->withErrors(['phone' => 'Enter a valid MTN or Airtel Uganda number, for example 0772 123 456.'])
                ->withInput();
        }

        $provider = UgPhone::provider($phone);

        if (! empty($validated['network']) && $validated['network'] !== $provider) {
            return back()
                ->withErrors(['phone' => 'That number does not look like a '.strtoupper($validated['network']).' number. Please check it or switch the network.'])
                ->withInput();
        }

        $order = GivingOrder::create([
            'reference' => (string) Str::uuid(),
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $phone,
            'amount' => $validated['amount'],
            'currency' => config('services.marzpay.currency', 'UGX'),
            'purpose' => $validated['purpose'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'provider' => $provider,
        ]);

        try {
            $result = $gateway->collect($order);
        } catch (\Throwable $exception) {
            report($exception);

            return back()
                ->withErrors(['amount' => 'We could not start the mobile money request. Please try again in a moment.'])
                ->withInput();
        }

        $order->update([
            'transaction_uuid' => $result['transaction_uuid'] ?: $order->reference,
            'provider' => $result['provider'] ?: $provider,
            'status' => $this->normalizeStatus($result['status']),
            'raw' => $result['raw'],
        ]);

        if ($gateway->isMock()) {
            return redirect()->route('giving.mock', $order->reference);
        }

        return redirect()->route('giving.pending', $order->reference);
    }

    public function pending(string $reference): View
    {
        $order = GivingOrder::where('reference', $reference)->firstOrFail();

        return view('site.giving-pending', compact('order'));
    }

    public function status(string $reference, MarzPayGateway $gateway): JsonResponse
    {
        $order = GivingOrder::where('reference', $reference)->firstOrFail();

        if (! $order->isFinal() && ! $gateway->isMock() && $order->transaction_uuid) {
            try {
                $this->applyStatus($order, $gateway->status($order));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return response()->json([
            'status' => $order->status,
            'final' => $order->isFinal(),
            'redirect' => route('giving.result', $order->reference),
        ]);
    }

    public function result(string $reference): View
    {
        $order = GivingOrder::where('reference', $reference)->firstOrFail();

        return view('site.giving-result', compact('order'));
    }

    public function webhook(Request $request): Response
    {
        $payload = $request->json()->all();

        if (isset($payload['data']) && is_array($payload['data']) && (isset($payload['data']['transaction']) || isset($payload['data']['event_type']))) {
            $payload = $payload['data'];
        }

        $reference = $payload['transaction']['reference'] ?? null;

        if (! $reference) {
            return response('No reference.', 200);
        }

        $order = GivingOrder::where('reference', $reference)->first();

        if (! $order || $order->isFinal()) {
            return response('OK', 200);
        }

        $event = (string) ($payload['event_type'] ?? '');
        $this->applyStatus($order, $this->callbackResult($payload, $event));

        return response('OK', 200);
    }

    public function mockGateway(string $reference, MarzPayGateway $gateway): View
    {
        abort_unless($gateway->isMock(), 404);

        $order = GivingOrder::where('reference', $reference)->firstOrFail();

        return view('site.giving-mock', compact('order'));
    }

    public function mockComplete(Request $request, string $reference, MarzPayGateway $gateway): RedirectResponse
    {
        abort_unless($gateway->isMock(), 404);

        $order = GivingOrder::where('reference', $reference)->firstOrFail();
        $approved = $request->input('outcome') === 'approve';

        if (! $order->isFinal()) {
            $payload = $this->mockCallbackPayload($order, $approved);
            $this->applyStatus($order, $this->callbackResult($payload, $payload['event_type']));
        }

        return redirect()->route('giving.result', $order->reference);
    }

    /**
     * @return array<int, string>
     */
    private function purposes(): array
    {
        return collect(explode(',', SiteSetting::value('giving_purposes')))
            ->map(fn (string $purpose) => trim($purpose))
            ->filter()
            ->values()
            ->all();
    }

    private function applyStatus(GivingOrder $order, array $result): void
    {
        $order->status = $result['status'];
        $order->provider = $result['provider'] ?: $order->provider;

        if (! empty($result['provider_transaction_id'])) {
            $order->provider_transaction_id = $result['provider_transaction_id'];
        }

        if (! empty($result['amount'])) {
            $order->amount = $result['amount'];
        }

        if ($result['charge'] !== null) {
            $order->charge = $result['charge'];
        }

        if ($result['net_amount'] !== null) {
            $order->net_amount = $result['net_amount'];
        }

        if (! empty($result['raw'])) {
            $order->raw = $result['raw'];
        }

        if ($order->status === 'completed' && ! $order->paid_at) {
            $order->paid_at = now();
        }

        $order->save();
    }

    private function callbackResult(array $payload, string $event): array
    {
        $transaction = $payload['transaction'] ?? [];
        $collection = $payload['collection'] ?? [];

        $status = $transaction['status'] ?? match (true) {
            str_contains($event, 'completed') => 'completed',
            str_contains($event, 'failed') => 'failed',
            str_contains($event, 'cancelled') => 'cancelled',
            default => 'processing',
        };

        return [
            'status' => $this->normalizeStatus($status),
            'provider' => $collection['provider'] ?? $transaction['provider'] ?? null,
            'provider_transaction_id' => $collection['provider_transaction_id'] ?? null,
            'amount' => $this->rawAmount($transaction['amount'] ?? $collection['amount'] ?? null),
            'charge' => $this->rawAmount($transaction['charge'] ?? $collection['charge'] ?? null),
            'net_amount' => $this->rawAmount($transaction['net_amount'] ?? $collection['net_amount'] ?? null),
            'raw' => $payload,
        ];
    }

    private function mockCallbackPayload(GivingOrder $order, bool $approved): array
    {
        $charge = (int) round($order->amount * 0.02);
        $net = $order->amount - $charge;
        $money = fn (int $value) => [
            'formatted' => number_format($value),
            'raw' => $value,
            'currency' => $order->currency,
        ];

        return [
            'event_type' => $approved ? 'collection.completed' : 'collection.failed',
            'transaction' => [
                'uuid' => $order->transaction_uuid,
                'reference' => $order->reference,
                'status' => $approved ? 'completed' : 'failed',
                'amount' => $money($order->amount),
                'charge' => $money($charge),
                'net_amount' => $money($net),
                'provider' => $order->provider,
                'phone_number' => $order->phone,
            ],
            'collection' => [
                'provider' => $order->provider,
                'phone_number' => $order->phone,
                'amount' => $money($order->amount),
                'charge' => $money($charge),
                'net_amount' => $money($net),
                'mode' => 'mock',
                'provider_transaction_id' => 'MOCK-'.strtoupper(Str::random(10)),
            ],
            'metadata' => [
                ['donationId' => (string) $order->id],
            ],
        ];
    }

    private function normalizeStatus(?string $status): string
    {
        return match ($status) {
            'completed', 'success' => 'completed',
            'failed', 'failure' => 'failed',
            'cancelled', 'canceled' => 'cancelled',
            'pending' => 'pending',
            default => 'processing',
        };
    }

    private function rawAmount(mixed $money): ?int
    {
        if (is_array($money)) {
            return isset($money['raw']) ? (int) $money['raw'] : null;
        }

        return is_numeric($money) ? (int) $money : null;
    }
}
