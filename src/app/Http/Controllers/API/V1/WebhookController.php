<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessWebhookJob;
use App\Models\WebhookEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Exception;

class WebhookController extends Controller
{
    /**
     * @TODO In the future implementation we can use Laravel Horizon for Queue Management.
     * I have added it in the system. Just need to spend more time to activate it.
     * @TODO In more complex and scalable application I will choose RabbitMQ / Kafka for the Queue
     * @TODO In future we need to accept providers dynamically and handle it with isolated WebhookProcessingInterface
     *
     * It receives and use Laravel queues for the webhook events from the payment provider.
     * For now, I don't have time to implement redis and Laravel Horizon.
     * Responds immediately after receiving and logging the raw payload.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handleCoinbase(Request $request): JsonResponse
    {

        /*
        *  @TODO Real World use: Signature Verification (crucial for security)
        * It prevents forged webhook requests in a real-world application
        * Secret need to verify from configuration to ensure it's not hardcoded.
        * Minimal validation for the webhook endpoint:
        *    'id': The unique ID of the webhook event itself from Coinbase (evt_abc123)
        *    'type': The event type (charge:confirmed)
        *    'data.code': Coinbase's internal charge code (ABCDE123)
        *    'data.metadata.transaction_id': Our internal transaction_id (dcc59d3f...)
        */

        /*Signature verification code snippet*/
        /*

        $webhookSecret = config('services.coinbase.webhook_secret');
        $signatureHeader = $request->header('X-CC-Webhook-Signature');
        $payload = $request->getContent(); // Get the raw request body

        if (!$signatureHeader || !hash_equals(hash_hmac('sha256', $payload, $webhookSecret), $signatureHeader)) {
            Log::warning('Invalid Coinbase webhook signature', [
                'signature' => $signatureHeader,
                'payload_hash' => hash_hmac('sha256', $payload, $webhookSecret),
                'expected_secret_exists' => !empty($webhookSecret),
                'ip_address' => $request->ip(),
            ]);
            return response()->json(['error' => 'Invalid signature'], 403);
        }
        */

        try {
            $validatedPayload = $request->validate([
                'id' => 'required|string',
                'type' => 'required|string',
                'created_at' => 'required|date',
                'data.code' => 'required|string',
                'data.metadata.transaction_id' => 'required|uuid',
            ]);

            $webhookEvent = WebhookEvent::create([
                'id' => Str::uuid(),
                'provider' => 'coinbase',
                'event_type' => data_get($validatedPayload, 'type'),
                'transaction_id' => data_get($validatedPayload, 'data.metadata.transaction_id'),
                'received_at' => Carbon::now(),
                'raw_payload' => $request->all(),
                'attempt' => 0,
            ]);

            ProcessWebhookJob::dispatch($webhookEvent->id);

            return response()->json(['success' => true, 'message' => 'Webhook request received and processing initiated.']);

        } catch (ValidationException $e) {
            Log::error('Coinbase webhook initial validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook payload structure',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            Log::error('Unexpected error receiving Coinbase webhook', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
            ]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
}
