<?php

namespace App\Support;

class UgPhone
{
    private const MTN_PREFIXES = ['76', '77', '78', '39', '31'];

    private const AIRTEL_PREFIXES = ['70', '74', '75', '20'];

    /**
     * Normalise a Ugandan mobile number to E.164 (+256XXXXXXXXX), or null when invalid.
     */
    public static function normalize(?string $input): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $input);

        if (str_starts_with($digits, '256')) {
            $local = substr($digits, 3);
        } elseif (str_starts_with($digits, '0')) {
            $local = substr($digits, 1);
        } else {
            $local = $digits;
        }

        if (strlen($local) !== 9) {
            return null;
        }

        return '+256'.$local;
    }

    /**
     * Detect the mobile money network for an E.164 Ugandan number.
     */
    public static function provider(?string $e164): ?string
    {
        if (! $e164 || ! str_starts_with($e164, '+256') || strlen($e164) !== 13) {
            return null;
        }

        $prefix = substr($e164, 4, 2);

        return match (true) {
            in_array($prefix, self::MTN_PREFIXES, true) => 'mtn',
            in_array($prefix, self::AIRTEL_PREFIXES, true) => 'airtel',
            default => null,
        };
    }

    public static function isMobile(?string $e164): bool
    {
        return self::provider($e164) !== null;
    }
}
