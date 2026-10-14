<?php

namespace App\Services\MarzPay;

use App\Models\GivingOrder;

interface MarzPayGateway
{
    public function isMock(): bool;

    /**
     * Initiate a mobile money collection for the given order.
     *
     * @return array{transaction_uuid: ?string, provider: ?string, status: string, raw: array}
     */
    public function collect(GivingOrder $order): array;

    /**
     * Fetch the current status of a collection for the given order.
     *
     * @return array{status: string, provider: ?string, provider_transaction_id: ?string, amount: ?int, charge: ?int, net_amount: ?int, raw: array}
     */
    public function status(GivingOrder $order): array;

    public function callbackUrl(): string;
}
