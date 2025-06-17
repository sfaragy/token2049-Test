<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case NEW = 'new';

    // Payment received but waiting for blockchain confirmations
    case PENDING_CONFIRMATION = 'pending_confirmation';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case EXPIRED = 'expired';
    case REFUNDED = 'refunded';
    case UNRESOLVED = 'unresolved';
}
