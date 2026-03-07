<?php

use App\Enums\PaymentIntentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_intents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('amount');
            $table->char('currency', 3)->default('USD');
            $table->string('status')->default(PaymentIntentStatus::RequiresPaymentMethod->value);
            $table->string('payment_method')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('failure_code')->nullable();
            $table->string('failure_message')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->string('idempotency_key')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_intents');
    }
};
