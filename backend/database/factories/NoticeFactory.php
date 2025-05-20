<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notice>
 */
class NoticeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {

        return [
            'title' => $this->faker->sentence(),
            'content' => $this->faker->paragraph(),
            'published_by' => User::factory(),           // ID of user who published the notice
            'department_id' => Department::factory(), // Assigning the random department ID
            'published_on' => $this->faker->date(), // Optional: set published time
            'archived_on' => $this->faker->optional()->date(), // Optional: set archived time
            'file' => $this->faker->optional()->url(), // Optional: set file name (random)
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
