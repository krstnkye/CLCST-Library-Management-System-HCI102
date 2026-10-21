<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_books', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('author');
            $table->string('isbn', 32)->unique();
            $table->string('call_number', 80)->nullable();
            $table->string('category', 100)->nullable()->index();
            $table->unsignedSmallInteger('publication_year')->nullable();
            $table->unsignedInteger('total_copies')->default(1);
            $table->unsignedInteger('available_copies')->default(1);
            $table->string('shelf_location', 100)->nullable();
            $table->string('status', 30)->default('active')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_books');
    }
};
