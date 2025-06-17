<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventLog extends Model
{
    use HasFactory;

    protected $table = 'event_logs';

    protected $fillable = [
        'webhook_event_id',
        'status',
        'message',
    ];

    /**
     * Get a webhook event that this log belongs to.
     *
     */
    public function webhookEvent(): BelongsTo
    {
        return $this->belongsTo(WebhookEvent::class, 'webhook_event_id', 'id');
    }
}
