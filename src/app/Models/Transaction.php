<?php

namespace App\Models;

use App\Enums\TransactionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'amount',
        'transaction_id',
        'status',
    ];

    protected $casts = [
        'status' => TransactionStatus::class,
    ];
}
