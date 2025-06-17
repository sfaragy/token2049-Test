<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\CryptoCurrency;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory, HasUuids;
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'transaction_id',
        'provider',
        'status',
        'crypto_amount',
        'crypto_currency',
        'network_fee',
        'transaction_hash',
        'address_used',
        'paid_at',
    ];

    protected $casts = [
        'crypto_amount' => 'decimal:8',
        'network_fee' => 'decimal:8',
        'crypto_currency' => CryptoCurrency::class,
        'paid_at' => 'datetime',
        'status' => PaymentStatus::class,
    ];

    /**
     * Get the transaction that this payment belongs to.
     *
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }
}
