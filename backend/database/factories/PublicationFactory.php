<?php

namespace Database\Factories;

use App\Models\Publication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Publication>
 */
class PublicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Publication::class;
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(6),
            'doi' => $this->faker->unique()->word(),
            'abstract' => $this->faker->paragraph(3),
            'journal' => $this->faker->company.' Journal',
            'volume' => $this->faker->numberBetween(1, 100),
            'issue' => $this->faker->numberBetween(1, 12),
            'pages' => $this->faker->numberBetween(1, 300).'-'.$this->faker->numberBetween(301, 500),
            'published_date' => $this->faker->dateTimeBetween('-10 years')->format('Y-m-d'),
            'url' => $this->faker->url(),
            'pdf_link' => $this->faker->url().'.pdf',
        ];
    }
}
