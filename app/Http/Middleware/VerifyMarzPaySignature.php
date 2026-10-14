<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMarzPaySignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.marzpay.webhook_secret');

        if (! empty($secret)) {
            $header = (string) $request->header('X-MarzPay-Signature');

            if ($header === '') {
                return response('Missing signature.', 401);
            }

            $parts = [];
            foreach (explode(',', $header) as $piece) {
                [$key, $value] = array_pad(explode('=', $piece, 2), 2, null);
                if ($key !== null && $value !== null) {
                    $parts[trim($key)] = trim($value);
                }
            }

            $timestamp = $parts['t'] ?? (string) $request->header('X-MarzPay-Timestamp');
            $signature = $parts['v1'] ?? null;

            if ($timestamp === '' || $signature === null) {
                return response('Malformed signature.', 401);
            }

            $expected = hash_hmac('sha256', $timestamp.'.'.$request->getContent(), (string) $secret);

            if (! hash_equals(strtolower($expected), strtolower($signature))) {
                return response('Invalid signature.', 401);
            }
        }

        return $next($request);
    }
}
