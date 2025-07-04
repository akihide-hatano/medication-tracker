<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\TimingTag; // TimingTagモデルをuse
use Database\Factories\TimingTagFactory; // TimingTagFactoryをuse

class TimingTagCrudTest extends TestCase
{
    use RefreshDatabase;

    // 認証済みユーザーが新しい薬を作成できるかテストする
    public function test_auth_user_create_timingtag():void{

        //ユーザーを作成し、ログイン状態にする。
        $user = User::factory()->create();
        $this->actingAs($user);

        //薬のページにアクセスして、ステータスコードが成功であること
        $response = $this->get(route('timing_tags.create'));
        $response->assertStatus(200);

        //内服薬のcode
        $timigTagData = [
            'timing_name' => 'テストタイミング',
        ];
        $response = $this->post(route('timing_tags.store'), $timigTagData);


        // 4. 薬が正常に保存され、一覧ページにリダイレクトされたか、成功メッセージが表示されたかなどを確認
        $response->assertRedirect(route('timing_tags.index')); // リダイレクト先を確認
        $response->assertSessionHas('success', '服用タイミングが正常に追加されました！'); // セッションに成功メッセージがあるか確認

        // 5. データベースに薬が実際に保存されているか確認
        $this->assertDatabaseHas('timing_tags', $timigTagData);
        $this->assertDatabaseCount('timing_tags', 1); // データベースに薬が1件追加されたことを確認
    }

    //タイミングタグの作成時に無効なデータでバリデーションエラーが発生する
    public function test_timing_tag_store_timingtag():void{
    //userの作成
    $user = User::factory()->create();
    $this->actingAs($user);

    //errorになるPostリクエストの作成
    $invalidData = [
    'timing_name' => '',
    ];

    $response = $this->post(route('timing_tags.store'), $invalidData);
    //リダイレクトされるかどうかを確認
    $response->assertStatus(302);
    // 各エラーメッセージが存在することを確認
    $response->assertSessionHasErrors(['timing_name']);
    // データベースに薬が作成されていないことを確認
    $this->assertDatabaseCount('timing_tags', 0);
    }

    public function test_user_view_timing_tag_index():void{

        //userのログインを作成
        $user = User::factory()->create();
        $this->actingAs($user);

        //テスト用のタイミングタグを複数作成する
        $timingTag1 = TimingTag::factory()->create(['timing_name' => '朝食前']);
        $timingTag2 = TimingTag::factory()->create(['timing_name' => '夕食後']);
        $timingTag3 = TimingTag::factory()->create(['timing_name' => '就寝前']);

        //タイミングタグの一覧pageにGETリクエストを送信
        $response = $this->get(route('timig_tags.index'));

        // ステータスコードが200 OKであることを確認
        $response->assertStatus(200);

        // 作成した全てのタグの名前がページに表示されていることを確認
        $response->assertSee($timingTag1->timing_name);
        $response->assertSee($timingTag2->timing_name);
        $response->assertSee($timingTag3->timing_name);
    }
}