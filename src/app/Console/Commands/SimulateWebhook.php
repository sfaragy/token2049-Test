<?php

namespace App\Console\Commands;

use App\Http\Controllers\API\V1\WebhookController;
use App\Services\CoinbaseWebhookDataFactory;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SimulateWebhook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * run this command with: php artisan simulate:webhook
     *
     * @var string
     */
    protected $signature = 'simulate:webhook
                            {--transaction_id= : The transaction UUID}
                            {--email= : The customer email}
                            {--status= : The status (e.g. PENDING, COMPLETED)}
                            {--timestamp= : Timestamp in ISO format (optional)}
                            {--count=1 : Number of webhook simulations to run}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Simulates a Coinbase webhook by sending a mock payload to our Token2049 /webhook endpoint';


    /**
     * @param CoinbaseWebhookDataFactory $dataFactory
     * @return int
     *
     */
    public function handle(CoinbaseWebhookDataFactory $dataFactory): int
    {
        $inputTransactionId = $this->option('transaction_id');
        $email = $this->option('email') ?? 'token2049@token2049.com';
        $desiredStatus = strtoupper($this->option('status') ?? '');
        $inputTimestamp = $this->option('timestamp');
        $count = (int)$this->option('count') ?? 1;

        $maxCount = $dataFactory->getDataSetCount();
        if ($count < 1 || $count > $maxCount) {
            $this->error("The --count option must be between 1 and {$maxCount}.");
            return self::FAILURE;
        }

        $allSuccessful = true;

        for ($i = 0; $i < $count; $i++) {
            $this->info("\n... Simulating Webhook #" . ($i + 1) . " ...");

            $dynamicData = $dataFactory->getDataSet($i);
            $transactionId = $inputTransactionId ?? (string) Str::uuid();
            $timestamp = $inputTimestamp ?? Carbon::now()->addMinutes($i * 2)->toISOString();

            $finalPaymentStatus = !empty($desiredStatus) ? $desiredStatus : $dynamicData['payment_status_coinbase'];
            $finalWebhookType = $dataFactory->mapPaymentStatusToWebhookType($finalPaymentStatus);

            $timeline = [
                ["time" => Carbon::parse($timestamp)->subMinutes(5)->toISOString(), "status" => "PENDING"],
                ["time" => $timestamp, "status" => $finalPaymentStatus],
            ];

            if ($finalWebhookType === 'charge:created') {
                $timeline = [["time" => $timestamp, "status" => "NEW"]];
            }

            $payload = [
                "id" => $dynamicData['webhook_id'],
                "type" => $finalWebhookType,
                "created_at" => $timestamp,
                "data" => [
                    "code" => $dynamicData['charge_code'],
                    "metadata" => [
                        "transaction_id" => $transactionId,
                        "email" => $email,
                    ],
                    "timeline" => $timeline,
                    "payments" => [
                        [
                            "network" => $dynamicData['network'],
                            "transaction_id" => $dynamicData['tx_hash'],
                            "status" => $finalPaymentStatus,
                            "value" => [
                                "amount" => $dynamicData['amount'],
                                "currency" => $dynamicData['currency']
                            ],
                        ],
                    ],
                ],
            ];

            $this->info('Sending Coinbase-like webhook payload:');
            $this->line(json_encode($payload, JSON_PRETTY_PRINT));
            $request = Request::create('/api/v1/webhook', 'POST', [], [], [], [
                'CONTENT_TYPE' => 'application/json',
            ], json_encode($payload));

            $response = app(WebhookController::class)->handleCoinbase($request);
            $data = json_decode($response->getContent(), true);

            $this->info("Response Status: " . $response->status());

            Log::info('Response', [$response, $data ]);

            if ($response->status() < 200 || $response->status() >= 300) {
                $allSuccessful = false;
            }
        }

        return $allSuccessful ? self::SUCCESS : self::FAILURE;
    }
}
