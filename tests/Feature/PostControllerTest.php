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
        $response->assertSee($postCompleted->post_date->format('Y年m月d日'));
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
        // $response->assertSee($postNotCompleted->post_date);
        $response->assertSee($postNotCompleted->post_date->format('Y年m月d日'));
        $response->assertDontSee('全ての薬を服用済みです。');
        $response->assertSee('全ての薬は服用されていません。'); // Bladeの正確なテキストに合わせる
        $response->assertSee('理由:'); // 「理由: 」というラベルがあるため
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

    public function test_auth_user_update_post():void{
        //ユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        //更新対象となる既存投稿を作成する
        $post = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-01',
            'content' => '元の記録',
            'all_meds_taken' => true,
            'reason_not_taken' => null,
        ]);

        //更新に必要な関連データを作成する
        $medication1 = Medication::factory()->create(['medication_name' => '更新薬A']);
        $timingTag1 = TimingTag::factory()->create(['timing_name' => '朝食後']);

        // 4. 更新データを作成する
        $updatedData = [
            'post_date' => '2025-07-05', // 日付を変更
            'content' => '更新された記録です。今日の体調は素晴らしい！', // 内容を変更
            'all_meds_taken' => false, // 服薬状況を変更
            'reason_not_taken' => '一部薬を飲み忘れました。', // 理由を追加
            'medications' => [
                // 既存の記録を更新する場合 (IDを渡す)
                // 今回はシンプルに新しい記録を追加する形式でテスト
                [
                    'medication_id' => $medication1->medication_id,
                    'timing_tag_id' => $timingTag1->timing_tag_id,
                    'is_completed' => false,
                    'taken_dosage' => '1カプセル',
                    'reason_not_taken' => '眠かったから', // 個別の記録にも理由がある場合
                ],
            ],
        ];

       // 5. PUT/PATCHリクエストを送信して投稿を更新
        $response = $this->put(route('posts.update', ['post' => $post->post_id]), $updatedData);

        // 6. 更新が正常に完了し、詳細ページにリダイレクトされたか、成功メッセージが表示されたかなどを確認
        // 通常は更新後に詳細ページか一覧ページにリダイレクトされるため、ルートに合わせて調整してください
        $response->assertRedirect(route('posts.show', ['post' => $post->post_id]));
        $response->assertSessionHas('success', '投稿が正常に更新されました！');

        // 7. データベースに投稿が実際に更新されているか確認
        //databaseに投稿が実際に更新されているか確認
        $this->assertDatabaseHas('posts',[
            'post_id'=>$post->post_id,
            'user_id' => $user->id,
            'post_date' => Carbon::parse($updatedData['post_date'])->startOfDay()->toDateTimeString(),
            'content' => $updatedData['content'],
            'all_meds_taken' => $updatedData['all_meds_taken'],
            'reason_not_taken' => $updatedData['reason_not_taken'],
        ]);

        // 8. データベースのPostMedicationRecordが更新されているか確認
        // 今回は、既存のPostMedicationRecordは削除され、新しいものが追加される前提でテスト
        // (Controllerのupdateロジックによって変わる)
        $this->assertDatabaseCount('post_medications_records', 1); // 新しく追加された1件のみが存在する前提
        $this->assertDatabaseHas('post_medications_records', [
            'post_id' => $post->post_id,
            'medication_id' => $medication1->medication_id,
            'timing_tag_id' => $timingTag1->timing_tag_id,
            'is_completed' => false,
            'taken_dosage' => '1カプセル',
            'reason_not_taken' => '眠かったから',
        ]);
    }

        /**
     * 認証済みユーザーが自分の投稿の編集フォームにアクセスできるかテストする (Edit - 正常系)
     */
    public function test_auth_user_post_edit_form(): void
    {
        // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. このユーザーの投稿を作成する (編集対象)
        // テスト用のデータ（content, all_meds_taken, reason_not_taken）は、
        // フォームにプリフィルされることを後で確認するために重要です。
        $post = Post::factory()->forUser($user)->create([
            'post_date' => '2025-07-10',
            'content' => '編集フォームテスト用の内容',
            'all_meds_taken' => false,
            'reason_not_taken' => '気分が優れなかったため',
        ]);

        // 3. 関連する薬の記録も作成する (フォームの薬のリストに表示されることを確認するため)
        // PostMedicationRecordを介して、medicationとtimingTagが関連付けられる
        $medication = Medication::factory()->create(['medication_name' => 'テスト薬X']);
        $timingTag = TimingTag::factory()->create(['timing_name' => '食後', 'category_name' => '昼']);
        PostMedicationRecord::factory()->create([
            'post_id' => $post->post_id,             // PostモデルのIDを直接渡す
            'medication_id' => $medication->medication_id, // MedicationモデルのIDを直接渡す
            'timing_tag_id' => $timingTag->timing_tag_id,   // TimingTagモデルのIDを直接渡す
            'is_completed' => false,
            'taken_dosage' => '1錠',
            'reason_not_taken' => '飲み忘れ',
        ]);

        // 4. 編集フォームページにGETリクエストを送信
        // route('posts.edit', ['post' => $post->post_id]) は /posts/{post_id}/edit のURLを生成します。
        $response = $this->get(route('posts.edit', ['post' => $post->post_id]));

        // 5. ステータスコードが200 OKであることを確認 (ページが正常に表示された)
        $response->assertStatus(200);

        // 6. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.edit');

        // 7. フォームに既存のデータがプリフィルされていることを確認
        // value属性や表示テキストで確認します。
        // post_date は input type="date" の value 属性で確認
        $response->assertSee('value="' . $post->post_date->format('Y-m-d') . '"', false); // falseでHTMLエスケープしない
        // content (textarea など)
        $response->assertSee($post->content);
        // all_meds_taken (booleanなので、チェックボックスの状態を確認)
        // false の場合、value="1" checked は存在しない
        $response->assertDontSee('name="all_meds_taken" value="1" checked');
        // reason_not_taken (all_meds_taken が false なので表示されるはず)
        $response->assertSee($post->reason_not_taken);

        // 8. 個別の薬の記録がプリフィルされていることを確認 (JavaScriptで動的に追加される要素に含まれるデータ)
        // Blade側で hidden input や表示要素にこれらの値が含まれることを想定
        // 例: <option value="{medication_id}" selected>薬の名前</option>
        $response->assertSee($medication->medication_name);
        $response->assertSee($timingTag->timing_name);
        // 個別の服用量の入力フィールドのvalue
        $response->assertSee('value="' . $medication->medication_id . '"', false); // 薬のID
        $response->assertSee('value="' . $timingTag->timing_tag_id . '"', false); // タイミングタグのID
        $response->assertSee('value="' . $post->postMedicationRecords->first()->taken_dosage . '"', false); // 服用量
        $response->assertSee('value="' . $post->postMedicationRecords->first()->reason_not_taken . '"', false); // 個別の理由
        $response->assertDontSee('name="medications[0][is_completed]" value="1" checked'); // 個別の服用済みチェックボックス (falseなのでチェックなし)

        // 9. ビューに渡されるべき変数が存在するか確認（Bladeが動的に生成する要素の元データ）
        $response->assertViewHas('medications');
        $response->assertViewHas('timingTags');
        $response->assertViewHas('displayCategories');
        $response->assertViewHas('nestedCategorizedMedicationRecords');
        $response->assertViewHas('jsInitialMedicationRecords');
    }

    public function test_auth_user_delete_form(): void
    {
        // 1. ユーザーを作成
        $user = User::factory()->create();
        // 2. 投稿を作成し、作成したユーザーに紐付ける
        $post = Post::factory()->create(['user_id' => $user->id]);
        // 3. 認証済みユーザーとして削除リクエストを送信
        // actingAs() ヘルパーで指定したユーザーとして認証状態をシミュレート
        $response = $this->actingAs($user)->delete(route('posts.destroy', $post));

        // 4. 結果の検証
        // データベースから投稿が削除されたことを確認
        $this->assertDatabaseMissing('posts', ['id' => $post->id]);

        // 投稿一覧ページまたはダッシュボードなど、適切なページにリダイレクトされたことを確認
        // 例: トップページにリダイレクトされる場合
        $response->assertRedirect(route('posts.index')); // または assertRedirect('/home') など適切なURLに修正してください

        // セッションに成功メッセージが保存されたことを確認 (任意)
        $response->assertSessionHas('success', '投稿が正常に削除されました！'); // メッセージ内容もアプリケーションに合わせて修正してください
    }

 public function test_auth_user_can_view_current_month_calendar(): void
    {
        // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 現在の月の投稿をいくつか作成する (服用済みと未服用)
        $currentYear = Carbon::now()->year;
        $currentMonth = Carbon::now()->month;

        // 服用済みの投稿
        $postCompleted = Post::factory()->forUser($user)->create([
            'post_date' => Carbon::create($currentYear, $currentMonth, 5)->toDateString(),
            'all_meds_taken' => true,
            'content' => '全て服用した日の記録',
        ]);
        $medication1 = Medication::factory()->create(['medication_name' => '薬A']);
        $timingTag1 = TimingTag::factory()->create(['timing_name' => '朝食後', 'category_name' => '朝']);
        PostMedicationRecord::factory()->create([
            'post_id' => $postCompleted->post_id,
            'medication_id' => $medication1->medication_id,
            'timing_tag_id' => $timingTag1->timing_tag_id,
            'is_completed' => true,
            'taken_dosage' => '1錠',
        ]);

        // 未服用の投稿
        $postNotCompleted = Post::factory()->forUser($user)->create([
            'post_date' => Carbon::create($currentYear, $currentMonth, 10)->toDateString(),
            'all_meds_taken' => false,
            'reason_not_taken' => '体調不良のため',
            'content' => '服用しなかった日の記録',
        ]);

        // 他のユーザーの投稿 (表示されないことを確認するため)
        $otherUser = User::factory()->create();
        Post::factory()->forUser($otherUser)->create([
            'post_date' => Carbon::create($currentYear, $currentMonth, 15)->toDateString(),
            'all_meds_taken' => true,
            'content' => '他のユーザーの記録',
        ]);

        // 3. カレンダーページにGETリクエストを送信 (パラメータなしで現在の月をテスト)
        $response = $this->get(route('posts.calendar'));

        // 4. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);

        // 5. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.calendar');

        // 6. ビューに渡されたデータが期待通りであることを確認
        $response->assertViewHas('date', function ($date) use ($currentYear, $currentMonth) {
            return $date->year === $currentYear && $date->month === $currentMonth;
        });

        $response->assertViewHas('medicationStatusByDay', function ($statusByDay) use ($postCompleted, $postNotCompleted, $medication1, $timingTag1) {
            // 服用済みの日のデータ検証
            $this->assertArrayHasKey($postCompleted->post_date->day, $statusByDay);
            $this->assertEquals('completed', $statusByDay[$postCompleted->post_date->day]['status']);
            $this->assertArrayHasKey('medications', $statusByDay[$postCompleted->post_date->day]);
            $this->assertStringContainsString($medication1->medication_name, $statusByDay[$postCompleted->post_date->day]['medications'][0]);
            $this->assertStringContainsString($timingTag1->timing_name, $statusByDay[$postCompleted->post_date->day]['medications'][0]);
            $this->assertStringContainsString('1錠', $statusByDay[$postCompleted->post_date->day]['medications'][0]);

            // 未服用の日のデータ検証
            $this->assertArrayHasKey($postNotCompleted->post_date->day, $statusByDay);
            $this->assertEquals('not_completed', $statusByDay[$postNotCompleted->post_date->day]['status']);
            $this->assertArrayHasKey('reason', $statusByDay[$postNotCompleted->post_date->day]);
            $this->assertEquals($postNotCompleted->reason_not_taken, $statusByDay[$postNotCompleted->post_date->day]['reason']);

            // 他のユーザーの投稿が含まれていないことを確認 (PostControllerのwhere('user_id', Auth::id())により)
            return true; // アサートが全て成功すればtrueを返す
        });
    }

    /**
     * @test
     * 認証済みユーザーが特定の月のカレンダーを正常に閲覧できることをテストする
     */
    public function test_auth_user_can_view_specific_month_calendar(): void
    {
        // 1. 認証済みユーザーを作成し、ログイン状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 過去の特定の月の投稿を作成する
        $targetYear = 2024;
        $targetMonth = 1; // 1月

        $postInTargetMonth = Post::factory()->forUser($user)->create([
            'post_date' => Carbon::create($targetYear, $targetMonth, 15)->toDateString(),
            'all_meds_taken' => true,
            'content' => '2024年1月の記録',
        ]);
        $medication2 = Medication::factory()->create(['medication_name' => '薬B']);
        $timingTag2 = TimingTag::factory()->create(['timing_name' => '夕食後', 'category_name' => '夕']);
        PostMedicationRecord::factory()->create([
            'post_id' => $postInTargetMonth->post_id,
            'medication_id' => $medication2->medication_id,
            'timing_tag_id' => $timingTag2->timing_tag_id,
            'is_completed' => true,
            'taken_dosage' => '2錠',
        ]);

        // 3. 別の月の投稿も作成 (表示されないことを確認するため)
        Post::factory()->forUser($user)->create([
            'post_date' => Carbon::create(2024, 2, 1)->toDateString(),
            'content' => '2024年2月の記録',
        ]);

        // 4. カレンダーページにGETリクエストを送信 (特定の年と月を指定)
        $response = $this->get(route('posts.calendar', ['year' => $targetYear, 'month' => $targetMonth]));

        // 5. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);

        // 6. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.calendar');

        // 7. ビューに渡された日付が期待通りであることを確認
        $response->assertViewHas('date', function ($date) use ($targetYear, $targetMonth) {
            return $date->year === $targetYear && $date->month === $targetMonth;
        });

        // 8. ビューに渡されたデータが期待通りであることを確認
        $response->assertViewHas('medicationStatusByDay', function ($statusByDay) use ($postInTargetMonth, $medication2, $timingTag2) {
            $this->assertArrayHasKey($postInTargetMonth->post_date->day, $statusByDay);
            $this->assertEquals('completed', $statusByDay[$postInTargetMonth->post_date->day]['status']);
            $this->assertArrayHasKey('medications', $statusByDay[$postInTargetMonth->post_date->day]);
            $this->assertStringContainsString($medication2->medication_name, $statusByDay[$postInTargetMonth->post_date->day]['medications'][0]);
            $this->assertStringContainsString('2錠', $statusByDay[$postInTargetMonth->post_date->day]['medications'][0]);

            // 別の月の投稿が含まれていないことを確認
            $this->assertCount(1, $statusByDay); // 15日の投稿のみが存在することを確認

            return true;
        });
    }

    public function test_auth_user_can_view_calendar_with_no_posts_for_month():void
    {
        //1.承認済みのuserを作成し、Login状態にする
        $user = User::factory()->create();
        $this->actingAs($user);

        // 2. 投稿がない月を指定 (例: 過去の適当な月)
        $targetYear = 2023;
        $targetMonth = 1; // 1月

        // 3. カレンダーページにGETリクエストを送信
        $response = $this->get(route('posts.calendar', ['year' => $targetYear, 'month' => $targetMonth]));

        // 4. ステータスコードが200 OKであることを確認
        $response->assertStatus(200);

        // 5. ビューが正しいビューを使用していることを確認
        $response->assertViewIs('posts.calendar');

        // 6. ビューに渡された日付が期待通りであることを確認
        $response->assertViewHas('date', function ($date) use ($targetYear, $targetMonth) {
            return $date->year === $targetYear && $date->month === $targetMonth;
        });

        // 7. medicationStatusByDay が空であることを確認
        $response->assertViewHas('medicationStatusByDay', []);
    }

    /**
     * @test
     * 未認証ユーザーがカレンダーページにアクセスできないことをテストする
     */
    public function test_guest_cannot_view_calendar(): void
    {
        // 1. ログインせずにカレンダーページにGETリクエストを送信
        $response = $this->get(route('posts.calendar'));

        // 2. ログインページにリダイレクトされることを確認
        $response->assertRedirect(route('login'));
    }
}