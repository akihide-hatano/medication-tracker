<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\Medication;
use App\Models\TimingTag;
use App\Models\PostMedicationRecord; // PostMedicationRecord モデルを追加
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Arr; // 必要に応じて利用

class PostMedicationRecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $medications = Medication::all();
        $timingTags = TimingTag::all();

        // 必須データが存在しない場合は警告を出して中断
        if ($posts->isEmpty()) {
            $this->command->info('PostSeeder が実行されていません。先に PostSeeder を実行してください。');
            return;
        }
        if ($medications->isEmpty()) {
            $this->command->info('MedicationSeeder が実行されていません。先に MedicationSeeder を実行してください。');
            return;
        }
        if ($timingTags->isEmpty()) {
            $this->command->info('TimingTagSeeder が実行されていません。先に TimingTagSeeder を実行してください。');
            return;
        }

        // 各投稿（日ごと）に対して、服用記録を作成
        foreach($posts as $post){
            // その投稿に紐付けられたタイミングタグ（posts_timingテーブル経由）を取得
            // withPivot('is_completed') で posts_timing 中間テーブルの is_completed も取得
            $postTimigTags = $post->timingTags()->get();

            foreach($postTimigTags as $timingTag){
            // そのタイミングタグに紐付けられた薬（medication_timing_tagsテーブル経由）を取得
            $associatedMedications = $timingTag->medications()->get();

                foreach ($associatedMedications as $medication) {
                    // その日のその薬、そのタイミングでの服用記録が既に存在しない場合のみ作成
                    // (post_id, medication_id, timing_tag_id の組み合わせでユニークと仮定)
                    if (PostMedicationRecord::where('post_id', $post->post_id)
                                            ->where('medication_id', $medication->medication_id)
                                            ->where('timing_tag_id', $timingTag->timing_tag_id)
                                            ->doesntExist()) {
                        
                        // 服用したかどうかをランダムで決定 (例えば80%の確率で服用したとする)
                        $isCompleted = (rand(1, 100) <= 80); 
                        $takenDosage = null;
                        $takenAt = null;
                        $reasonNotTaken = null;

                        if ($isCompleted) {
                            // 服用した場合は、服用量と服用日時を設定
                            // dosageカラムから数値部分を抽出し、それを参考にランダムな量を設定
                            preg_match('/(\d+)/', $medication->dosage, $matches);
                            $baseDosage = isset($matches[1]) ? (int)$matches[1] : 1; // dosageから数値部分を取得、なければ1
                            
                            $takenDosage = $baseDosage; // 基本はそのままの量を服用
                            // 少しだけランダムなバリエーションを付けることも可能
                            // $takenDosage = $baseDosage * rand(1, 2); 

                            // 服用日時を、投稿日の午前中から深夜までのランダムな時刻に設定
                            $takenAt = Carbon::parse($post->post_date)
                                            ->addHours(rand(0, 23)) // 0時から23時
                                            ->addMinutes(rand(0, 59)) // 0分から59分
                                            ->addSeconds(rand(0, 59)); // 0秒から59秒
                        } else {
                            // 服用しなかった場合は、理由をランダムで設定 (2パターン)
                            $reasons = [
                                '飲み忘れ',
                                '体調不良のため服用せず',
                                'すでに服用済みだったため',
                                '副作用が心配だったため',
                            ];
                            $reasonNotTaken = Arr::random($reasons); // ランダムに理由を選択
                        }

                        // レコードの作成
                        PostMedicationRecord::create([
                            'post_id' => $post->post_id,
                            'medication_id' => $medication->medication_id,
                            'timing_tag_id' => $timingTag->timing_tag_id, // ここでタイミングタグIDを紐付ける
                            'is_completed' => $isCompleted,
                            'taken_dosage' => $takenDosage,
                            'taken_at' => $takenAt,
                            'reason_not_taken' => $reasonNotTaken,
                        ]);
                    }
                }
            }
        }
    }
}
