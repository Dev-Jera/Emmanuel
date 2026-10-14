<?php

namespace App\Services\MarzPay;

use RuntimeException;

class MarzPayException extends RuntimeException
{
    public function __construct(string $message, public array $context = [])
    {
        parent::__construct($message);
    }
}
