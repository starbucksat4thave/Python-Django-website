<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed students by year, semester, and session
        $studentData = [
            ['year' => 1, 'semester' => 1, 'session' => '2022'],
            ['year' => 2, 'semester' => 1, 'session' => '2021'],
            ['year' => 2, 'semester' => 2, 'session' => '2020'],
            ['year' => 3, 'semester' => 2, 'session' => '2019'],
            ['year' => 4, 'semester' => 2, 'session' => '2018'],
        ];

        foreach ($studentData as $data) {
            User::factory(5)->create()->each(function ($user) use ($data) {
                $user->assignRole('student');
                $user->update($data);
            });
        }

        // Seed teachers
        User::factory(5)->create()->each(function ($user) {
            $user->assignRole('teacher');
        });

        // Admin user
        $adminUser = User::factory()->create([
            'name' => 'Admin User 1',
            'image' => fake()->imageUrl(),
            'email' => 'admin@example.com',
            'designation' => 'staff',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'city' => 'Dhaka',
            'department_id' => 1,
            'dob' => '1995-01-01',
            'phone' => '01712345678',
            'university_id' => 123459,
            'publication_count' => 4,
        ]);
        $adminUser->assignRole('admin');

        // Super admin user
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'image' => fake()->imageUrl(),
            'email' => 'superadmin@example.com',
            'designation' => 'staff',
            'password' => Hash::make('password'),
            'status' => 'active',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
            'city' => 'Pabna',
            'department_id' => 1,
            'dob' => '1986-03-01',
            'phone' => '01712344563',
            'university_id' => 123456,
            'publication_count' => 4,
        ]);
        $superAdminUser->assignRole('super-admin');
    }
}
