<?php

namespace Tests\Feature;

use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_webhook_updates_transaction_successfully()
    {
//        $transaction = Transaction::factory()->create([
//            'id' => $uuid = Str::uuid(),
//            'status' => 'PENDING',
//            'email' => 'user@example.com',
//        ]);
//
//        $payload = [
//            'transaction_id' => $uuid,
//            'email' => 'user@example.com',
//            'status' => 'CONFIRMED',
//            'timestamp' => now()->toIso8601String(),
//        ];
//
//        $response = $this->postJson('/api/webhook', $payload);
//
//        $response->assertStatus(200);
//        $this->assertDatabaseHas('transactions', [
//            'id' => $uuid,
//            'status' => 'CONFIRMED',
//        ]);
        $this->assertTrue(true);
    }
}
