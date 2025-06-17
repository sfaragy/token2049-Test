<?php

namespace App\Services\Providers;

use App\Contracts\PaymentProviderInterface;
use InvalidArgumentException;
use \Illuminate\Foundation\Application;

class PaymentProviderManager
{
    protected $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function getProvider(string $name): PaymentProviderInterface
    {
        switch ($name) {
            case 'coinbase':
                return $this->app->make(CoinbaseProvider::class);
             case 'binance':
                 return $this->app->make(BinanceProvider::class);
            default:
                throw new InvalidArgumentException("Payment provider [{$name}] is not supported.");
        }
    }
}
