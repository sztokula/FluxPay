<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_logs', function (Blueprint $table): void {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
            $table->index(['user_id', 'happened_at']);
        });
    }

    public function down(): void
    {
        Schema::table('event_logs', function (Blueprint $table): void {
            $table->dropIndex('event_logs_user_id_happened_at_index');
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
