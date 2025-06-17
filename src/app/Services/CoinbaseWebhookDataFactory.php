<?php

namespace App\Services;

use Illuminate\Support\Str;

class CoinbaseWebhookDataFactory
{
    /**
     * Mocked data for webhook simulation scenarios.
     * 10 diverse sets of dynamic data for different simulation scenarios
     *  Mocked blockchain transaction hash
     *
     * @var array
     */
    private array $dynamicDataSets;

    public function __construct()
    {
        $this->dynamicDataSets = [
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'bitcoin',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:confirmed',
                'amount' => '0.002',
                'currency' => 'BTC',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'ethereum',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'PENDING',
                'type_coinbase' => 'charge:pending',
                'amount' => '0.05',
                'currency' => 'ETH',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'bitcoin',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'FAILED',
                'type_coinbase' => 'charge:failed',
                'amount' => '0.001',
                'currency' => 'BTC',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'litecoin',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:confirmed',
                'amount' => '0.5',
                'currency' => 'LTC',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'ethereum',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:resolved',
                'amount' => '0.1',
                'currency' => 'ETH',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'polygon',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:confirmed',
                'amount' => '100.00',
                'currency' => 'USDC',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'bitcoin',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'PENDING',
                'type_coinbase' => 'charge:pending',
                'amount' => '0.005',
                'currency' => 'BTC',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'ethereum',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'FAILED',
                'type_coinbase' => 'charge:failed',
                'amount' => '0.02',
                'currency' => 'ETH',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'dogecoin',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:confirmed',
                'amount' => '500.0',
                'currency' => 'DOGE',
            ],
            [
                'webhook_id' => 'evt_' . Str::random(20),
                'charge_code' => Str::random(10),
                'network' => 'bitcoincash',
                'tx_hash' => '0x' . Str::random(60),
                'payment_status_coinbase' => 'CONFIRMED',
                'type_coinbase' => 'charge:confirmed',
                'amount' => '0.1',
                'currency' => 'BCH',
            ],
        ];
    }

    /**
     * @param int $index
     * @return array
     */
    public function getDataSet(int $index): array
    {
        return $this->dynamicDataSets[$index % count($this->dynamicDataSets)];
    }

    /**
     * Gets the total number of available dynamic data sets.
     *
     * @return int
     */
    public function getDataSetCount(): int
    {
        return count($this->dynamicDataSets);
    }

    /**
     * Maps a given payment status string.
     *
     */
    public function mapPaymentStatusToWebhookType(string $status): string
    {
        return match (strtoupper($status)) {
            'NEW' => 'charge:created',
            'PENDING' => 'charge:pending',
            'PENDING_CONFIRMATION' => 'charge:pending',
            'COMPLETED' => 'charge:confirmed',
            'FAILED' => 'charge:failed',
            'REFUNDED' => 'charge:refunded',
            'UNRESOLVED' => 'charge:resolved',
            default => 'charge:confirmed',
        };
    }
}
