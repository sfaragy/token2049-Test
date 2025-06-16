<?php

namespace App\Jobs;

use App\Services\CheckoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $data;

    /**
     * Create a new job instance.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(CheckoutService $checkoutService): void
    {
        $updated = $checkoutService->updateStatus($this->data['transaction_id'], $this->data['status']);

        if (! $updated) {
            throw new \Exception('Transaction not found: ' . $this->data['transaction_id']);
        }
    }

    /* @todo we can chunk the number of process in queue to consider.*/

}
