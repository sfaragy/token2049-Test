<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $primaryKey = 'transaction_id';
    protected $fillable = [
        'transaction_id',
        'email',
        'amount',
        'currency',
        'status',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
        'amount' => 'decimal:2'
    ];

    /**
     * Get the payments for the transaction.
     *
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'transaction_id', 'transaction_id');
    }

    /**
     * Get the webhook events associated with the transaction.
     *
     */
    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class, 'transaction_id', 'transaction_id');
    }
}
