<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\TimingTag;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class PostTimingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $posts = Post::all();
        $timingTags = TimingTag::all();

        if ($posts->isEmpty()) {
            $this->command->info('PostSeeder が実行されていません。先に PostSeeder を実行してください。');
            return;
        }
        if ($timingTags->isEmpty()) {
            $this->command->info('TimingTagSeeder が実行されていません。先に TimingTagSeeder を実行してください。');
            return;
        }

        foreach($posts as $post){
            // 各投稿に割り当てるタイミングタグの数をランダムに決める（2～4個）
            // ただし、利用可能なタグの数を超えないようにする
            $numTagsToAssign = rand(2,min(4,$timingTags->count()));

            // 全てのタイミングタグからランダムに$numTagsToAssign個選択
            $selectedTimingTags = $timingTags->random($numTagsToAssign);

            foreach ($selectedTimingTags as $timingTag) {
                // Post と TimingTag の間にリレーションを作成（中間テーブルへの挿入）
                // firstOrCreate を使用して、既に存在しない場合のみ作成
                $post->timingTags()->firstOrCreate([
                    'timing_tag_id' => $timingTag->timing_tag_id,
                    'is_completed' => rand(0, 1), // そのタイミングが完了したかどうかをランダムで設定
                ]);
            }
        }
    }
}
