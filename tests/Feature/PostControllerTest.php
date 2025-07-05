<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Post;
use App\Models\Medication;
use App\Models\TimingTag;
use App\Models\PostMedicationRecord;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Vite;

class PostControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 各テストの前に実行される処理
     */
    protected function setUp(): void
    {
        parent::setUp();
        // Viteのビルドファイルをテスト時に読み込まないようにモックする
        // `@vite` ディレクティブは `__invoke` メソッドを呼び出す
        Vite::shouldReceive('__invoke')
            ->andReturnUsing(function ($entrypoints) {
                $html = '';
                foreach ((array) $entrypoints as $entrypoint) {
                    // 環境によっては、CSSファイルも含まれる可能性があるため、適切に処理
                    if (str_ends_with($entrypoint, '.css')) {
                        $html .= "<link rel=\"stylesheet\" href=\"/build/assets/{$entrypoint}\">";
                    } else {
                        $html .= "<script src=\"/build/assets/{$entrypoint}\"></script>";
                    }
                }
                return $html;
            });
    }

    /**
     * 認証済みユーザーが自分の投稿一覧を閲覧できるかテストする (Index - 正常系)
     */
    public function test_auth_user_posts_index(): void
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

    /**
     * 認証済みユーザーが自分の投稿一覧を閲覧できるかテストする (Index - 正常系)
     */
    public function test_auth_user_posts_details(): void
    {
        // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);
        // 2. このユーザーの投稿をいくつか作成する
        $postCompleted = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-01',
            'content' => '今日の記録1',
            'all_meds_taken' => true,
            'reason_not_taken'=>null,
        ]);
        //3.投稿詳細pageにGetリクエストを送信
        $response = $this->get(route('posts.show', ['post' => $postCompleted->post_id]));
        // 4. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);
        // 5. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.show');
        // 6. 投稿の詳細内容が表示されていることを確認
        $response->assertSee($postCompleted->content);
        $response->assertSee($postCompleted->post_date);
        // all_meds_taken が true の場合に関連するテキストを確認
        // 例: "全ての薬を服用済み" のようなテキストが表示されることを想定
        $response->assertSee('全ての薬を服用済み'); // Bladeに表示されるテキストに合わせる
        $response->assertDontSee('服用しなかった理由'); // 理由が表示されないことを確認

        // 7. 別の投稿を作成 (all_meds_taken が false の場合)
        $postNotCompleted = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-02',
            'content' => '今日の記録：一部未服用です。',
            'all_meds_taken' => false,
            'reason_not_taken' => '気分が悪かったため。',
        ]);
        // 8. 別の投稿の詳細ページにGETリクエストを送信
        $response = $this->get(route('posts.show', ['post' => $postNotCompleted->post_id]));
        $response->assertStatus(200);
        $response->assertViewIs('posts.show');
        $response->assertSee($postNotCompleted->content);
        $response->assertSee($postNotCompleted->post_date);
        // all_meds_taken が false の場合に関連するテキストを確認
        $response->assertDontSee('全ての薬を服用済み');
        $response->assertSee('服用しなかった理由'); // 理由が表示されることを確認
        $response->assertSee($postNotCompleted->reason_not_taken);
    }


    /**
     * 認証済みユーザーが新しい投稿の作成フォームにアクセスできるかテストする (Create - 正常系)
     */
    public function test_auth_user_create_post(): void
    {
         // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);
        // 2. フォームに表示される必要のあるデータを作成しておく（これらは直接assertSeeしない）
        $medication = Medication::factory()->create(['medication_name' => 'テスト薬A']);
        $timingTag = TimingTag::factory()->create(['timing_name' => '朝食後', 'category_name' => '朝']);
        // 3. 作成フォームページにGETリクエストを送信
        $response = $this->get(route('posts.create'));
        // 4. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);
        // 5. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.create');
        // 6. ビューに medication と timingTags データが渡されていることを確認
        $response->assertViewHas('medications');
        $response->assertViewHas('timingTags');
        $response->assertViewHas('displayCategories');
        $response->assertViewHas('nestedCategorizedMedicationRecords');
        // 7. フォーム上に表示されるべき静的なテキストを確認
        // 薬の記録がない場合のメッセージが表示されることを期待
        $response->assertSee('薬の記録がありません。下のボタンで追加してください。');
    }
}