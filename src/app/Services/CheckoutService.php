<?php

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\DTOs\CheckoutRequestData;
use App\DTOs\CheckoutResponseData;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use App\Services\Providers\PaymentProviderManager;
use Illuminate\Support\Str;

class CheckoutService
{
    protected PaymentProviderInterface $provider;
    protected PaymentProviderManager $providerManager;

    public function __construct(PaymentProviderManager $providerManager)
    {
        $this->providerManager = $providerManager;
    }

    /**
     *  THis method will choose coinbase as a default provider, but we will be able to pass any provider from controller to instantiate.
     *
     * @param CheckoutRequestData $data
     * @param string $providerName
     * @return CheckoutResponseData
     */
    public function createCheckout(CheckoutRequestData $data, string $providerName = 'coinbase'): CheckoutResponseData
    {
        $provider = $this->providerManager->getProvider($providerName);

        $transaction = Transaction::create([
            'email' => $data->email,
            'amount' => $data->amount,
            'currency' => 'USD',
            'transaction_id' => Str::uuid(),
            'status' => TransactionStatus::PENDING,
        ]);

        $checkoutUrl = $provider->createPaymentSession($transaction);

        return new CheckoutResponseData($checkoutUrl, $transaction->transaction_id);
    }
}
