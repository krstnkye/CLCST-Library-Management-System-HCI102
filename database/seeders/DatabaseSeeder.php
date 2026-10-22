<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            return;
        }

        $accounts = [
            ['STU-2026-001', 'Juan Dela Cruz', 'student@clcst.edu.ph', 'student', 'BS Computer Science'],
            ['FAC-2026-001', 'Roberto Mendoza', 'faculty@clcst.edu.ph', 'faculty', 'College of Computer Studies'],
            ['LIB-2026-001', 'Maria Santos', 'librarian@clcst.edu.ph', 'librarian', 'Main Library'],
            ['ADM-2026-001', 'Library Administrator', 'admin@clcst.edu.ph', 'admin', 'Library Administration'],
            ['SYS-2026-001', 'System Administrator', 'superadmin@clcst.edu.ph', 'superadmin', 'System Administration'],
        ];

        foreach ($accounts as [$patronId, $name, $email, $role, $department]) {
            [$first, $last] = explode(' ', $name, 2);
            User::updateOrCreate(['email' => $email], [
                'patron_id' => $patronId,
                'name' => $name,
                'first_name' => $first,
                'last_name' => $last,
                'role' => $role,
                'status' => 'active',
                'program_or_department' => $department,
                'mobile_number' => '09171234567',
                'approved_at' => now(),
                'password' => Hash::make('LibraryDemo2026!'),
            ]);
        }

        // One application waiting in Account Review.
        User::updateOrCreate(['email' => 'applicant@clcst.edu.ph'], [
            'patron_id' => 'STU-2026-014',
            'name' => 'Ana Reyes',
            'first_name' => 'Ana',
            'last_name' => 'Reyes',
            'role' => 'student',
            'status' => 'pending',
            'program_or_department' => 'BS Information Technology',
            'mobile_number' => '09181234567',
            'password' => Hash::make(Str::password(20)),
        ]);

        DB::transaction(fn () => $this->call(LibraryDemoSeeder::class));
    }
}
