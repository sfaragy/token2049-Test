<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WebhookEvent extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'webhook_events';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'provider',
        'event_type',
        'transaction_id',
        'received_at',
        'raw_payload',
        'attempt',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    /**
     * Get the logs associated with the webhook event.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(EventLog::class, 'webhook_event_id', 'id');
    }

    /**
     * Get a transaction associated with the webhook event.
     *
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'transaction_id', 'transaction_id');
    }
}
