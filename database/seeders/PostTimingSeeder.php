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
            $numTagsToAssign = rand(2,min(4,$timingTags->count()));
            $selectedTimingTags = $timingTags->random($numTagsToAssign);

            // この投稿に紐付けるタイミングタグのIDとピボットデータを収集
            $syncData = [];
            foreach ($selectedTimingTags as $timingTag) {
                $isCompleted = (rand(0, 1) === 1); // 50%の確率でtrue/false
                $syncData[$timingTag->timing_tag_id] = ['is_completed' => $isCompleted];
            }

            // syncWithoutDetaching を使用してまとめてアタッチ
            // 既に存在する場合は更新され、存在しない場合は作成される
            $post->timingTags()->syncWithoutDetaching($syncData);
        }
    }
}
