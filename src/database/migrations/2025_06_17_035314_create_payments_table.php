<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('transaction_id');
            $table->string('provider');
            $table->string('status');

            $table->decimal('crypto_amount', 20, 8)->nullable();
            $table->string('crypto_currency')->nullable();
            $table->decimal('network_fee', 20, 8)->nullable();
            $table->string('transaction_hash')->nullable()->unique(); //Blockchain transaction_id (Optional for our case study)
            $table->string('address_used')->nullable(); // Crypto Address (Optional for our case study)
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->foreign('transaction_id')
                ->references('transaction_id')->on('transactions')
                ->onDelete('cascade');
            $table->index('transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
