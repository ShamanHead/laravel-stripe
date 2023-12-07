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
        Schema::create('subscription_payment_intent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subscription_id')->references('id')->on('subscriptions');
            $table->foreignId('payment_intent_id')->references('id')->on('payment_intent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_payment_intent');
    }
};
