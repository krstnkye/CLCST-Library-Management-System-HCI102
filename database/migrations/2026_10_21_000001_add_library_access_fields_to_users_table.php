<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('patron_id', 100)->nullable()->unique()->after('id');
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('middle_initial', 5)->nullable()->after('last_name');
            $table->string('role', 30)->default('student')->index()->after('email');
            $table->string('status', 30)->default('pending')->index()->after('role');
            $table->string('program_or_department', 150)->nullable()->after('status');
            $table->string('mobile_number', 30)->nullable()->after('program_or_department');
            $table->string('credential_path')->nullable()->after('mobile_number');
            $table->timestamp('approved_at')->nullable()->after('credential_path');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete()->after('approved_at');
            $table->timestamp('last_login_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['approved_by']);
            $table->dropUnique(['patron_id']);
            $table->dropIndex(['role']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'patron_id', 'first_name', 'last_name', 'middle_initial', 'role',
                'status', 'program_or_department', 'mobile_number', 'credential_path',
                'approved_at', 'approved_by', 'last_login_at',
            ]);
        });
    }
};
