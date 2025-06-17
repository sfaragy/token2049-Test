<?php

namespace App\Services\Providers;

use App\Contracts\PaymentProviderInterface;
use App\Models\Transaction;

/* @TODO Simulated payment URL for Token2049 case study project. In the future we will migrate it to better way for real world.
 * In real-world use we will ensure DTO for the createPaymentSession with more realistic scenario.
 */
class CoinbaseProvider implements PaymentProviderInterface
{
    public function createPaymentSession(Transaction $transaction): string
    {
        return "https://fake.coinbase.com/pay/" . $transaction->transaction_id;
    }
}
