<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            DepartmentSeeder::class,
            UserSeeder::class,
            CourseSeeder::class,
//            CourseSessionSeeder::class,
//            EnrollmentSeeder::class,
            NoticeSeeder::class,
            ApplicationTemplateSeeder::class,

        ]);
    }
}
