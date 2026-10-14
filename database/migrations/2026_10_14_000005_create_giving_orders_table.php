<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('giving_orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('reference')->unique();
            $table->string('transaction_uuid')->nullable();
            $table->string('provider_transaction_id')->nullable();
            $table->string('provider', 20)->nullable();
            $table->string('name', 120);
            $table->string('email', 180)->nullable();
            $table->string('phone', 32);
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('charge')->nullable();
            $table->unsignedBigInteger('net_amount')->nullable();
            $table->string('currency', 8)->default('UGX');
            $table->string('purpose', 120)->nullable();
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending');
            $table->json('raw')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('giving_orders');
    }
};
