<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->cascadeOnDelete();
            $table->string('type', 32);
            $table->unsignedBigInteger('amount_cents');
            $table->foreignId('counterpart_wallet_id')->nullable()->constrained('wallets')->nullOnDelete();
            $table->uuid('transfer_group_id')->nullable()->index();
            $table->string('status', 16)->default('completed');
            $table->foreignId('reverses_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->foreignId('reversed_by_transaction_id')->nullable()->constrained('transactions')->nullOnDelete();
            $table->string('idempotency_key', 64)->nullable()->unique();
            $table->timestamps();

            $table->index(['wallet_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
