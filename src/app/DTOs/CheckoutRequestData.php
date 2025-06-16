<?php

namespace App\DTOs;

class CheckoutRequestData
{
    public function __construct(
        public readonly string $email,
        public readonly float $amount
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            amount: (float) $data['amount'],
        );
    }
}
