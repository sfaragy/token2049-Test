<?php
namespace App\Contracts;

use App\Models\Transaction;

interface PaymentProviderInterface
{
    /**
     * @TODO Creating a crypto checkout session (Simulating for now). In the future, we will migrate it to better way for real world.
     */
    public function createPaymentSession(Transaction $transaction): string;
}
