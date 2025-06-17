<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_transaction()
    {
        $payload = [
            'email' => 'buyer@example.com',
            'amount' => 100,
        ];

        $response = $this->postJson('/api/v1/checkout', $payload);
        //Log::info('Checkout Test', [$response]);
        $response->assertStatus(200)
            ->assertJsonStructure(['success', 'data' => ['checkout_url', 'transaction_id']]);

        $transactionId = $response->json('data.transaction_id');

        $this->assertNotNull($transactionId);
        $this->assertDatabaseHas('transactions', [
            'email' => 'buyer@example.com',
            'transaction_id' => $transactionId,
            'amount' => 100,
        ]);
    }
}
