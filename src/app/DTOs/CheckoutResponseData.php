<?php

namespace App\DTOs;

class CheckoutResponseData
{
    public function __construct(
        public readonly string $checkout_url,
        public readonly string $transaction_id,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            checkout_url: $data['checkout_url'],
            transaction_id: $data['transaction_id'],
        );
    }

    /**
     * This method Convert the DTO to an array for JSON response.
     *
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            'checkout_url' => $this->checkout_url,
            'transaction_id' => $this->transaction_id,
        ];
    }
}
