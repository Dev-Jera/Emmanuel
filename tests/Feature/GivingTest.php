<?php

namespace Tests\Feature;

use App\Models\GivingOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GivingTest extends TestCase
{
    use RefreshDatabase;

    private function validDonation(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Grace Namutebi',
            'email' => 'grace@example.com',
            'phone' => '0772 123 456',
            'amount' => 20000,
            'network' => 'mtn',
            'purpose' => 'Offering',
            'message' => 'Blessings',
        ], $overrides);
    }

    private function callbackPayload(GivingOrder $order, string $event = 'collection.completed'): array
    {
        $status = $event === 'collection.completed' ? 'completed' : 'failed';

        return [
            'event_type' => $event,
            'transaction' => [
                'uuid' => $order->transaction_uuid,
                'reference' => $order->reference,
                'status' => $status,
                'amount' => ['formatted' => '20,000.00', 'raw' => 20000, 'currency' => 'UGX'],
                'charge' => ['formatted' => '400.00', 'raw' => 400, 'currency' => 'UGX'],
                'net_amount' => ['formatted' => '19,600.00', 'raw' => 19600, 'currency' => 'UGX'],
                'provider' => 'mtn',
                'phone_number' => '+256772123456',
            ],
            'collection' => [
                'provider' => 'mtn',
                'phone_number' => '+256772123456',
                'amount' => ['formatted' => '20,000.00', 'raw' => 20000, 'currency' => 'UGX'],
                'charge' => ['formatted' => '400.00', 'raw' => 400, 'currency' => 'UGX'],
                'net_amount' => ['formatted' => '19,600.00', 'raw' => 19600, 'currency' => 'UGX'],
                'mode' => 'mtnuganda',
                'provider_transaction_id' => '148769164724',
            ],
            'metadata' => [['donationId' => (string) $order->id]],
        ];
    }

    public function test_giving_page_renders(): void
    {
        $this->get('/giving')
            ->assertOk()
            ->assertSee('Give from the heart');
    }

    public function test_mock_donation_creates_order_and_redirects_to_mock_gateway(): void
    {
        $response = $this->post('/giving', $this->validDonation());

        $order = GivingOrder::sole();
        $response->assertRedirect(route('giving.mock', $order->reference));

        $this->assertSame('+256772123456', $order->phone);
        $this->assertSame('mtn', $order->provider);
        $this->assertSame('processing', $order->status);
        $this->assertSame(20000, $order->amount);
    }

    public function test_mock_approval_completes_the_order(): void
    {
        $this->post('/giving', $this->validDonation());
        $order = GivingOrder::sole();

        $this->post(route('giving.mock.complete', $order->reference), ['outcome' => 'approve'])
            ->assertRedirect(route('giving.result', $order->reference));

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertNotNull($order->net_amount);
        $this->assertNotNull($order->provider_transaction_id);
    }

    public function test_invalid_phone_is_rejected(): void
    {
        $this->post('/giving', $this->validDonation(['phone' => '12345']))
            ->assertSessionHasErrors('phone');

        $this->assertSame(0, GivingOrder::count());
    }

    public function test_amount_outside_limits_is_rejected(): void
    {
        $this->post('/giving', $this->validDonation(['amount' => 100]))
            ->assertSessionHasErrors('amount');

        $this->assertSame(0, GivingOrder::count());
    }

    public function test_webhook_marks_order_completed(): void
    {
        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'transaction_uuid' => '22222222-2222-4222-8222-222222222222',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $this->postJson('/giving/webhook', $this->callbackPayload($order))->assertOk();

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertSame(19600, $order->net_amount);
        $this->assertSame('148769164724', $order->provider_transaction_id);
    }

    public function test_webhook_is_idempotent(): void
    {
        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'transaction_uuid' => '22222222-2222-4222-8222-222222222222',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $this->postJson('/giving/webhook', $this->callbackPayload($order))->assertOk();
        $paidAt = $order->refresh()->paid_at;

        $this->postJson('/giving/webhook', $this->callbackPayload($order))->assertOk();

        $this->assertSame($paidAt->toDateTimeString(), $order->refresh()->paid_at->toDateTimeString());
    }

    public function test_webhook_accepts_dashboard_wrapper(): void
    {
        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'transaction_uuid' => '22222222-2222-4222-8222-222222222222',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $wrapped = [
            'event_type' => 'collection.completed',
            'webhook_id' => 12,
            'business_id' => 34,
            'timestamp' => now()->toIso8601String(),
            'data' => $this->callbackPayload($order),
        ];

        $this->postJson('/giving/webhook', $wrapped)->assertOk();

        $this->assertSame('completed', $order->refresh()->status);
    }

    public function test_webhook_rejects_bad_signature_when_signing_enabled(): void
    {
        config(['services.marzpay.webhook_secret' => 'whsec_test']);

        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $body = json_encode($this->callbackPayload($order));

        $this->call('POST', '/giving/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_MARZPAY_SIGNATURE' => 't='.time().',v1=deadbeef',
        ], $body)->assertStatus(401);
    }

    public function test_webhook_accepts_valid_signature(): void
    {
        config(['services.marzpay.webhook_secret' => 'whsec_test']);

        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $body = json_encode($this->callbackPayload($order));
        $timestamp = (string) time();
        $signature = hash_hmac('sha256', $timestamp.'.'.$body, 'whsec_test');

        $this->call('POST', '/giving/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
            'HTTP_X_MARZPAY_TIMESTAMP' => $timestamp,
            'HTTP_X_MARZPAY_SIGNATURE' => 't='.$timestamp.',v1='.$signature,
        ], $body)->assertOk();

        $this->assertSame('completed', $order->refresh()->status);
    }

    public function test_status_endpoint_reports_final_state(): void
    {
        $order = GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Grace',
            'phone' => '+256772123456',
            'amount' => 20000,
            'status' => 'processing',
            'provider' => 'mtn',
        ]);

        $this->getJson(route('giving.status', $order->reference))
            ->assertOk()
            ->assertJson(['status' => 'processing', 'final' => false]);

        $order->update(['status' => 'completed', 'paid_at' => now()]);

        $this->getJson(route('giving.status', $order->reference))
            ->assertOk()
            ->assertJson(['status' => 'completed', 'final' => true]);
    }

    public function test_admin_can_see_donations_tab(): void
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'secret-password',
        ]);

        GivingOrder::create([
            'reference' => '11111111-1111-4111-8111-111111111111',
            'name' => 'Grace Namutebi',
            'phone' => '+256772123456',
            'amount' => 20000,
            'net_amount' => 19600,
            'status' => 'completed',
            'provider' => 'mtn',
            'paid_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard', ['tab' => 'giving']))
            ->assertOk()
            ->assertSee('Donations')
            ->assertSee('Grace Namutebi')
            ->assertSee('19,600');
    }
}
