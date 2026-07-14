<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'Quản trị hệ thống',
                'email' => 'admin@lab.local',
                'role' => UserRole::Admin,
                'department' => 'IT',
                'staff_code' => 'ADM-001',
            ],
            [
                'name' => 'Trưởng phòng thí nghiệm',
                'email' => 'manager@lab.local',
                'role' => UserRole::Manager,
                'department' => 'Laboratory Management',
                'staff_code' => 'MGR-001',
            ],
            [
                'name' => 'Kỹ thuật viên Lab A',
                'email' => 'staff.a@lab.local',
                'role' => UserRole::LabStaff,
                'department' => 'Lab A',
                'staff_code' => 'STAFF-A-001',
            ],
            [
                'name' => 'Kỹ thuật viên Lab B',
                'email' => 'staff.b@lab.local',
                'role' => UserRole::LabStaff,
                'department' => 'Lab B',
                'staff_code' => 'STAFF-B-001',
            ],
            [
                'name' => 'Nguyễn An',
                'email' => 'researcher.an@lab.local',
                'role' => UserRole::Researcher,
                'department' => 'Biotechnology',
                'student_code' => 'RES-001',
            ],
            [
                'name' => 'Trần Bình',
                'email' => 'researcher.binh@lab.local',
                'role' => UserRole::Researcher,
                'department' => 'Environmental Science',
                'student_code' => 'RES-002',
            ],
        ];

        foreach ($users as $user) {
            User::query()->updateOrCreate(
                ['email' => $user['email']],
                [
                    ...$user,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );
        }
    }
}
