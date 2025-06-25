<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        if($users->isEmpty()){
            $this->command->info('UserSeeder が実行されていません。先に UserSeeder を実行してください。');
            return;
        }

        // 各ユーザーに対して過去の日付の投稿を作成する
        // ユーザーごとに日付のオフセットを設けることで、post_date の重複を避ける
        $userOffset = 0;
        foreach($users as $user){
            for($i = 0; $i < 30; $i++){
                // ユーザーごとに異なる日付範囲を生成
                // 例: ユーザー1は今日から過去29日、ユーザー2は30日前から過去59日、といった具合
                $date = Carbon::now()->subDays($i + ($userOffset * 30))->toDateString();

                // その日付の投稿がまだ存在しない場合のみ作成 (一応のチェック)
                // このロジック修正により、基本的には重複しなくなるはず
                if(Post::where('user_id',$user->id)->where('post_date',$date)->doesntExist()){
                    Post::create([
                        'user_id'=>$user->id,
                        'post_date'=>$date,
                        'all_meds_taken'=> (rand(1, 100) <= 95) ? true : false, // 95%の確率でtrue
                        'reason_not_taken' => (rand(1, 100) <= 5) ? '体調不良のため一部服用せず' : null, // 5%の確率で理由あり
                        'content' => (rand(0, 1) === 0) ? null : '今日は比較的体調が良かった。',
                    ]);
                }
            }
            $userOffset++; // 次のユーザーのためにオフセットを増やす
        }
    }
}
