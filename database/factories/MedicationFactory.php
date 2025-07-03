<?php

namespace Database\Factories;

use App\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Medication::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'medication_name' => $this->faker->unique()->word() . '薬', // unique() を追加して薬名が重複しないように
            'dosage' => $this->faker->randomElement(['1錠', '2錠', '5ml', '10mg']),
            'notes' => $this->faker->sentence(),
            'effect' => $this->faker->sentence(3),
            'side_effects' => $this->faker->sentence(2),
        ];
    }
}