<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Path on the private disk; photos are served through an authenticated route.
        Schema::table('users', function (Blueprint $table): void {
            $table->string('profile_photo_path')->nullable()->after('credential_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('profile_photo_path');
        });
    }
};
