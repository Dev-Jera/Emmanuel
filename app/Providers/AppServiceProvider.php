<?php

namespace App\Providers;

use App\Services\MarzPay\HttpMarzPayGateway;
use App\Services\MarzPay\MarzPayGateway;
use App\Services\MarzPay\MockMarzPayGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MarzPayGateway::class, function (): MarzPayGateway {
            $mock = filter_var(config('services.marzpay.mock'), FILTER_VALIDATE_BOOLEAN)
                || empty(config('services.marzpay.key'))
                || empty(config('services.marzpay.secret'));

            return $mock ? new MockMarzPayGateway : new HttpMarzPayGateway;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
