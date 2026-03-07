<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('idempotency_keys', function (Blueprint $table): void {
            $table->id();
            $table->string('idempotency_key');
            $table->string('route');
            $table->string('method');
            $table->string('request_hash');
            $table->unsignedSmallInteger('response_code');
            $table->json('response_body');
            $table->timestamps();

            $table->unique(['idempotency_key', 'route', 'method']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('idempotency_keys');
    }
};
