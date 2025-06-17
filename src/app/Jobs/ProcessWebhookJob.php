<?php

namespace App\Jobs;

use App\Enums\CryptoCurrency;
use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Models\EventLog;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\WebhookEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $webhookEventId;
    public $tries = 3;
    public $backoff = 30;

    /**
     * Create a new job instance.
     *
     * @param string $webhookEventId The ID of the WebhookEvent record to process.
     *
     */
    public function __construct(string $webhookEventId)
    {
        $this->webhookEventId = $webhookEventId;
    }

    /**
     *
     * Execute the job.
     *
     */
    public function handle(): void
    {
        $webhookEvent = WebhookEvent::find($this->webhookEventId);

        if (!$webhookEvent) {
            Log::error("WebhookEvent not found for ID: {$this->webhookEventId}. Skipping job.", [
                'webhook_event_id' => $this->webhookEventId
            ]);
            return;
        }

        $webhookEvent->increment('attempt');

        $payload = $webhookEvent->raw_payload;

        $coinbaseWebhookId = data_get($payload, 'id');
        $providerEventType = data_get($payload, 'type');
        $internalTransactionId = data_get($payload, 'data.metadata.transaction_id');

        $cryptoPayment = data_get($payload, 'data.payments.0');
        $cryptoAmount = data_get($cryptoPayment, 'value.amount');
        $cryptoCurrency = data_get($cryptoPayment, 'value.currency');
        $blockchainTransactionHash = data_get($cryptoPayment, 'transaction_id');
        $networkFee = data_get($cryptoPayment, 'network_fee');
        $webhookCreatedAt = Carbon::parse(data_get($payload, 'created_at'));

        if (EventLog::where('webhook_event_id', $webhookEvent->id)->where('status', 'processed')->exists()) {
            Log::info("WebhookEvent ID: {$webhookEvent->id} already processed. Skipping.", [
                'coinbase_webhook_id' => $coinbaseWebhookId,
                'internal_webhook_event_id' => $webhookEvent->id,
            ]);
            return;
        }

        $transaction = Transaction::where('transaction_id', $internalTransactionId)->first();

        if (!$transaction) {
            Log::warning('Associated Transaction not found for webhook event', [
                'webhook_event_id' => $webhookEvent->id,
                'internal_transaction_id' => $internalTransactionId,
                'coinbase_webhook_id' => $coinbaseWebhookId,
            ]);
            $this->logEvent($webhookEvent->id, 'failed', 'Associated transaction not found for processing.');
            return;
        }

        $paymentStatus = null;
        $newTransactionStatus = $transaction->status;

        switch ($providerEventType) {
            case 'charge:created':
                $paymentStatus = PaymentStatus::NEW;
                $newTransactionStatus = TransactionStatus::PENDING;
                break;
            case 'charge:pending':
                $paymentStatus = PaymentStatus::PENDING_CONFIRMATION;
                $newTransactionStatus = TransactionStatus::PENDING;
                break;
            case 'charge:confirmed':
                $paymentStatus = PaymentStatus::COMPLETED;
                $newTransactionStatus = TransactionStatus::COMPLETED;
                break;
            case 'charge:failed':
                $paymentStatus = PaymentStatus::FAILED;
                if ($newTransactionStatus !== TransactionStatus::COMPLETED && $newTransactionStatus !== TransactionStatus::REFUNDED) {
                    $newTransactionStatus = TransactionStatus::FAILED;
                }
                break;
            case 'charge:refunded':
                $paymentStatus = PaymentStatus::REFUNDED;
                $newTransactionStatus = TransactionStatus::REFUNDED;
                break;
            case 'charge:resolved':
                if(data_get($payload, 'data.timeline') && collect(data_get($payload, 'data.timeline'))->contains('status', 'COMPLETED')){
                    $paymentStatus = PaymentStatus::COMPLETED;
                    $newTransactionStatus = TransactionStatus::COMPLETED;
                } else {
                    Log::warning("Coinbase charge:resolved event with non-completed final status for transaction: {$internalTransactionId}", [
                        'webhook_event_id' => $webhookEvent->id,
                        'coinbase_webhook_id' => $coinbaseWebhookId,
                        'timeline' => data_get($payload, 'data.timeline'),
                    ]);

                    $paymentStatus = PaymentStatus::UNRESOLVED;
                }
                break;
            default:
                Log::warning('Unknown Coinbase webhook event type received for processing', [
                    'webhook_event_id' => $webhookEvent->id,
                    'event_type' => $providerEventType,
                    'coinbase_webhook_id' => $coinbaseWebhookId,
                ]);
                $this->logEvent($webhookEvent->id, 'failed', 'Unknown Coinbase webhook event type: ' . $providerEventType);
                return;
        }


        if (!$paymentStatus) {
            Log::error("Failed to map Coinbase event type '{$providerEventType}' to PaymentStatus for transaction: {$internalTransactionId}", [
                'webhook_event_id' => $webhookEvent->id,
                'coinbase_webhook_id' => $coinbaseWebhookId,
            ]);
            $this->logEvent($webhookEvent->id, 'failed', "Failed to map event type {$providerEventType} to payment status.");
            return;
        }

        /* Part of Database Transaction ACID */
        DB::transaction(function () use ($transaction, $paymentStatus, $newTransactionStatus, $webhookEvent, $cryptoAmount, $cryptoCurrency, $networkFee, $blockchainTransactionHash, $webhookCreatedAt, $coinbaseWebhookId) {

            $payment = Payment::updateOrCreate(
                [
                    'transaction_hash' => $blockchainTransactionHash,
                ],
                [
                    'id' => Str::uuid(),
                    'transaction_id' => $transaction->transaction_id,
                    'provider' => 'coinbase',
                    'status' => $paymentStatus,
                    'crypto_amount' => $cryptoAmount,
                    'crypto_currency' => $cryptoCurrency ? CryptoCurrency::tryFrom($cryptoCurrency) : null,
                    'network_fee' => $networkFee,
                    'address_used' => null,
                    'paid_at' => $paymentStatus === PaymentStatus::COMPLETED ? $webhookCreatedAt : null,
                ]
            );

            if ($transaction->status !== $newTransactionStatus && ($transaction->status !== TransactionStatus::COMPLETED || $newTransactionStatus === TransactionStatus::REFUNDED)) {
                $transaction->status = $newTransactionStatus;
                $transaction->save();
                Log::info("Transaction {$transaction->transaction_id} status updated from {$transaction->getOriginal('status')->value} to {$newTransactionStatus->value}.", [
                    'webhook_event_id' => $webhookEvent->id,
                    'coinbase_webhook_id' => $coinbaseWebhookId,
                ]);
            } else {
                Log::info("Transaction {$transaction->transaction_id} status remains {$transaction->status->value}.", [
                    'webhook_event_id' => $webhookEvent->id,
                    'coinbase_webhook_id' => $coinbaseWebhookId,
                ]);
            }

            $this->logEvent($webhookEvent->id, 'processed', "Webhook processed for Coinbase. Transaction status: {$newTransactionStatus->value}, Payment status: {$paymentStatus->value}.");
        });
    }

    /**
     *
     * Handle a job failure.
     *
     */
    public function failed(Throwable $exception): void
    {
        Log::error('ProcessWebhookJob failed permanently after retries', [
            'webhook_event_id' => $this->webhookEventId,
            'exception_message' => $exception->getMessage(),
            'exception_trace' => $exception->getTraceAsString(),
            'attempt' => $this->attempts(),
        ]);

        $this->logEvent($this->webhookEventId, 'failed', 'Job failed permanently: ' . $exception->getMessage());
    }

    /**
     * Helper method to create an EventLog entry.
     *
     */
    private function logEvent(string $webhookEventId, string $status, string $message): void
    {
        EventLog::create([
            'webhook_event_id' => $webhookEventId,
            'status' => $status,
            'message' => $message,
        ]);
    }
}
