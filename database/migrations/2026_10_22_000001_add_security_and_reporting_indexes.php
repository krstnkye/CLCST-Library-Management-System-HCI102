<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->index(['role', 'status'], 'users_role_status_index');
        });

        Schema::table('library_books', function (Blueprint $table): void {
            $table->index(['status', 'category'], 'library_books_status_category_index');
        });

        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->index(['event', 'created_at'], 'audit_logs_event_created_index');
            $table->index(['user_id', 'created_at'], 'audit_logs_user_created_index');
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->dropIndex('audit_logs_event_created_index');
            $table->dropIndex('audit_logs_user_created_index');
        });

        Schema::table('library_books', function (Blueprint $table): void {
            $table->dropIndex('library_books_status_category_index');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex('users_role_status_index');
        });
    }
};
