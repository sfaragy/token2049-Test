<?php

namespace App\Services\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\Transaction;

/* Future use only if Token2049 want to extend with other service provider.*/
class BinanceProvider implements PaymentProviderInterface
{
    public function createPaymentSession(Transaction $transaction): string
    {
        return "https://fake.binance.com/checkout/" . $transaction->transaction_id;
    }
}

