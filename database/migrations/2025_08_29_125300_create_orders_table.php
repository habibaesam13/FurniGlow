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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->bigInteger('amount')->default(0);
            $table->string('payment_method');
            $table->string('currency', 3)->default(config('services.stripe.currency', 'EGP'));
            $table->string('payment_intent_id')->nullable()->index();
            $table->string('client_secret')->nullable();
            $table->string('reference')->unique();        // uuid for idempotency
            $table->json('items')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
