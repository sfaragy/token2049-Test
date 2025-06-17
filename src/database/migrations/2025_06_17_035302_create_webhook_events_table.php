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
        Schema::create('webhook_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->string('event_type');
            $table->uuid('transaction_id');
            $table->timestamp('received_at');
            $table->json('raw_payload')->nullable();
            $table->integer('attempt')->default(0);
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
        Schema::dropIfExists('webhook_events');
    }
};
