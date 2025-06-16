<?php

namespace App\Enums;

enum TransactionStatus: string
{
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case PENDING = 'pending';
}
