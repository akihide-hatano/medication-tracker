<?php

namespace Database\Factories;

use App\Models\PostMedicationRecord;
use Illuminate\Database\Eloquent\Factories\Factory;
// 以下の use ステートメントは、definition() で直接呼び出さないなら不要になりますが、残しておいても問題ありません。
// use App\Models\Medication;
// use App\Models\TimingTag;
// use App\Models\Post;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PostMedicationRecord>
 */
class PostMedicationRecordFactory extends Factory
{
    /**
     * The name of the corresponding model.
     *
     * @var string
     */
    protected $model = PostMedicationRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // ★★★これらの行を削除またはコメントアウトします★★★
            // 'post_id' => Post::factory(),
            // 'medication_id' => Medication::factory(),
            // 'timing_tag_id' => TimingTag::factory(),

            'is_completed' => $this->faker->boolean,
            'taken_dosage' => $this->faker->optional(0.5)->word . ' ' . $this->faker->optional(0.5)->randomElement(['錠', 'ml', 'g', 'カプセル']),
            'reason_not_taken' => $this->faker->optional(0.3)->sentence,
        ];
    }
}