<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Enums\TransactionStatus;

class HealthController extends Controller
{
    public function status()
    {
        $stats = Transaction::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toDateTimeString(),
            'env' => config('app.env'),
            'stats' => [
                TransactionStatus::PENDING->value => $stats[TransactionStatus::PENDING->value] ?? 0,
                TransactionStatus::COMPLETED->value => $stats[TransactionStatus::COMPLETED->value] ?? 0,
                TransactionStatus::FAILED->value => $stats[TransactionStatus::FAILED->value] ?? 0,
            ]
        ]);
    }
}
