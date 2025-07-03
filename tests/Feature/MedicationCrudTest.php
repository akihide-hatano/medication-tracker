<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase; // テストごとにデータベースをクリーンな状態にする
use Illuminate\Foundation\Testing\WithFaker; // ダミーデータ生成用（今回は使わないが、一般的なので残しておく）
use Tests\TestCase; // Laravelの機能テストの基底クラス
use App\Models\User; // ユーザーモデル
use App\Models\Medication; // 薬モデル

class MedicationCrudTest extends TestCase
{
    use RefreshDatabase;

    // 認証済みユーザーが新しい薬を作成できるかテストする
    public function test_auth_user_create_medication():void{

        //ユーザーを作成し、ログイン状態にする。
        $user = User::factory()->create();
        $this->actingAs($user);

        //薬のページにアクセスして、ステータスコードが成功であること
        $response = $this->get(route('medications.create'));
        $response->assertStatus(200);

        //内服薬のcode
        $medicationData = [
            'medication_name' => 'テスト薬A',
            'dosage' => '1錠',
            'notes' => 'テスト用の薬の説明です。',
            'effect' => '効果テスト',
            'side_effects' => '副作用テスト',
        ];
        $response = $this->post(route('medications.store'), $medicationData);


        // 4. 薬が正常に保存され、一覧ページにリダイレクトされたか、成功メッセージが表示されたかなどを確認
        $response->assertRedirect(route('medications.index')); // リダイレクト先を確認
        $response->assertSessionHas('success', '薬が正常に追加されました！'); // セッションに成功メッセージがあるか確認

        // 5. データベースに薬が実際に保存されているか確認
        $this->assertDatabaseHas('medications', [
            'medication_name' => 'テスト薬A',
            'dosage' => '1錠',
            'notes' => 'テスト用の薬の説明です。',
            'effect' => '効果テスト',
            'side_effects' => '副作用テスト',
        ]);
        $this->assertDatabaseCount('medications', 1); // データベースに薬が1件追加されたことを確認
    }
}
