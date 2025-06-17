<?php

namespace Tests\Feature;

use App\Enums\TransactionStatus;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_updates_transaction_status_from_webhook()
    {
        $transaction = Transaction::factory()->create([
            'transaction_id' => 'dcc59d3f-5f7b-458f-8580-28083566973b',
            'email' => 'john@example.com',
            'status' => TransactionStatus::PENDING
        ]);

        $payload = [
            "id" => "evt_abc123",
            "type" => "charge:confirmed",
            "created_at" => "2025-06-16T12:34:56Z",
            "data" => [
                "code" => "ABCDE123",
                "metadata" => [
                    "transaction_id" => $transaction->transaction_id,
                    "email" => $transaction->email
                ],
                "timeline" => [
                    ["time" => "2025-06-16T12:00:00Z", "status" => "NEW"],
                    ["time" => "2025-06-16T12:10:00Z", "status" => "PENDING"],
                    ["time" => "2025-06-16T12:34:56Z", "status" => "COMPLETED"]
                ],
                "payments" => [
                    [
                        "network" => "bitcoin",
                        "transaction_id" => "0xabc",
                        "status" => "CONFIRMED",
                        "value" => [
                            "amount" => "0.002",
                            "currency" => "BTC"
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->postJson('/api/v1/webhook', $payload);
        Log::info('asdf', [$response]);
        $response->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('transactions', [
            'transaction_id' => $transaction->transaction_id,
            'status' => TransactionStatus::COMPLETED->value,
        ]);
    }
}
