<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'post_date' => $this->faker->date(),
            'content' => $this->faker->sentence(),
            'all_meds_taken' => $this->faker->boolean(),
            'reason_not_taken' => $this->faker->boolean(50) ? $this->faker->sentence() : null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'all_meds_taken' => true,
            'reason_not_taken' => null,
        ]);
    }

    public function notCompleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'all_meds_taken' => false,
            'reason_not_taken' => $this->faker->sentence(),
        ]);
    }

    public function onDate(string $date): static
    {
        return $this->state(fn (array $attributes) => [
            'post_date' => Carbon::parse($date)->format('Y-m-d'),
        ]);
    }
}