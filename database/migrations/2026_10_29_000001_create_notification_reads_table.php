<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Patron notifications are built from live records, so only the "read" mark is stored.
        Schema::create('notification_reads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('notification_key', 120);
            $table->timestamp('read_at');
            $table->unique(['user_id', 'notification_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_reads');
    }
};
