<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('library_books', function (Blueprint $table): void {
            $table->string('publisher', 150)->nullable()->after('author');
            $table->string('subject', 100)->nullable()->after('category');
            $table->string('loan_policy', 20)->default('circulating')->after('status');
            $table->string('cover_url')->nullable()->after('loan_policy');
            $table->index('title');
        });

        Schema::create('book_copies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('library_book_id')->constrained()->cascadeOnDelete();
            $table->string('barcode', 32)->unique();
            $table->string('copy_code', 20);
            $table->string('status', 20)->default('available')->index();
            $table->string('location', 120)->nullable();
            $table->timestamps();
            $table->unique(['library_book_id', 'copy_code']);
        });

        Schema::create('loans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('book_copy_id')->constrained()->restrictOnDelete();
            $table->foreignId('issued_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receipt_no', 40)->unique();
            $table->timestamp('borrowed_at');
            $table->timestamp('due_at')->index();
            $table->timestamp('returned_at')->nullable()->index();
            $table->unsignedTinyInteger('renewals')->default(0);
            $table->timestamps();
            $table->index(['user_id', 'returned_at']);
        });

        Schema::create('reservations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('library_book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20)->default('pending')->index();
            $table->timestamp('ready_until')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index(['library_book_id', 'status']);
        });

        Schema::create('fines', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('loan_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('settled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('reason', 150);
            $table->unsignedInteger('days_late')->default(0);
            $table->decimal('amount', 8, 2);
            $table->string('status', 20)->default('outstanding')->index();
            $table->string('receipt_no', 40)->nullable()->unique();
            $table->timestamp('settled_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('library_requests', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 40)->index();
            $table->string('subject', 200);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('submitted')->index();
            $table->text('response')->nullable();
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 150);
            $table->text('body');
            $table->string('audience', 20)->default('all')->index();
            $table->timestamp('published_at')->index();
            $table->timestamps();
        });

        Schema::create('library_settings', function (Blueprint $table): void {
            $table->string('key', 60)->primary();
            $table->string('value', 255);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_settings');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('library_requests');
        Schema::dropIfExists('fines');
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('book_copies');

        Schema::table('library_books', function (Blueprint $table): void {
            $table->dropIndex(['title']);
            $table->dropColumn(['publisher', 'subject', 'loan_policy', 'cover_url']);
        });
    }
};
