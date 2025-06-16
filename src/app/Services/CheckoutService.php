<?php

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\DTOs\CheckoutRequestData;
use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Support\Str;

class CheckoutService
{
    protected PaymentProviderInterface $provider;

    public function __construct(PaymentProviderInterface $provider)
    {
        $this->provider = $provider;
    }

    public function createCheckout(CheckoutRequestData $data): array
    {
        $transaction = Transaction::create([
            'email' => $data->email,
            'amount' => $data->amount,
            'transaction_id' => Str::uuid(),
        ]);

        $checkoutUrl = $this->provider->createPaymentSession($transaction);

        return ['transaction' => $transaction, 'checkout_url' => $checkoutUrl];
    }

    public function updateStatus(string $transactionId, string $status): ?Transaction
    {
        $transaction = Transaction::where('transaction_id', $transactionId)->first();

        if (!$transaction) {
            return null;
        }

        $transaction->status = TransactionStatus::from($status);
        $transaction->save();

        return $transaction;
    }
}
