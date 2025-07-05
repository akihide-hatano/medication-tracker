<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Medication; // 必要に応じてuse
use App\Models\TimingTag; // 必要に応じてuse
use App\Models\PostMedicationRecord; // 必要に応じてuse


class PostControllerTest extends TestCase
{
    use RefreshDatabase; // テストごとにデータベースをクリーンな状態にする

    /**
     * 認証済みユーザーが自分の投稿一覧を閲覧できるかテストする (Index - 正常系)
     */
    public function test_authenticated_user_can_view_their_posts_index(): void
    {
        // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. このユーザーの投稿をいくつか作成する
        $post1 = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-01',
            'content' => '今日の記録1',
            'all_meds_taken' => true,
        ]);
        $post2 = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-02',
            'content' => '今日の記録2',
            'all_meds_taken' => false,
        ]);

        // 3. 他のユーザーの投稿も作成する（これが表示されないことを確認するため）
        $otherUser = User::factory()->create();
        $otherPost = Post::factory()->forUser($otherUser)->create([
            'post_date' => '2025-07-03',
            'content' => '他のユーザーの記録',
            'all_meds_taken' => true,
        ]);

        // 4. 投稿一覧ページにGETリクエストを送信
        $response = $this->get(route('posts.index'));

        // 5. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);

        // 6. ログインユーザーの投稿が表示されていることを確認
        $response->assertSee($post1->content);
        $response->assertSee($post2->content);

        // 7. 他のユーザーの投稿が表示されていないことを確認
        $response->assertDontSee($otherPost->content);

        // 8. データベースの投稿数が期待通りであることを確認 (RefreshDatabaseにより、このテストでは3件のPostが存在する)
        $this->assertDatabaseCount('posts', 3);
    }
}