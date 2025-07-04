<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Medication;
use Database\Factories\MedicationFactory; // この行は正しいです

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

    public function test_auth_user_view_medication_index():void
    {
        //ユーザーのログイン
        $user = User::factory()->create();
        $this->actingAs($user);

                // テスト用の薬を複数作成する
        // これらの薬は特定のユーザーには紐づきません（グローバルなマスターリストのため）
        $medication1 = Medication::factory()->create([
            'medication_name' => 'テスト薬1',
            'dosage' => '10mg',
            'notes' => 'テストノート1',
            'effect' => '効果1',
            'side_effects' => '副作用1',
        ]);
        $medication2 = Medication::factory()->create([
            'medication_name' => 'テスト薬2',
            'dosage' => '20ml',
            'notes' => 'テストノート2',
            'effect' => '効果2',
            'side_effects' => '副作用2',
        ]);

        //内服薬の一覧pageがgetリクエストを送信しているか確認
        $response = $this ->get(route('medications.index'));

        //ステータスコードが200であること
        $response->assertStatus(200);


        // 作成した全ての薬の名前と、その他の詳細情報がページに表示されていることを確認
        // medication1 の情報
        $response->assertSee($medication1->medication_name);
        $response->assertSee($medication1->dosage);
        $response->assertSee($medication1->notes);
        $response->assertSee($medication1->effect);
        $response->assertSee($medication1->side_effects);

        // medication2 の情報
        $response->assertSee($medication2->medication_name);
        $response->assertSee($medication2->dosage);
        $response->assertSee($medication2->notes);
        $response->assertSee($medication2->effect);
        $response->assertSee($medication2->side_effects);

    }

    public function test_auth_show_medication():void{
        //ユーザーを作成してLogin状態であることを立証
        $user = User::factory()->create();
        $this->actingAs($user);

        //表示したい内服薬のdataを作成
        $medication = Medication::factory()->create([
                'medication_name' => '詳細テスト薬',
                'dosage' => '20ml',
                'notes' => 'この薬の詳細を確認するためのテストノートです。',
                'effect' => '詳細確認用効果',
                'side_effects' => '詳細確認用副作用',
        ]);
        //routeの設定
        $response = $this->get(route('medications.show', $medication->medication_id));
        //HTTPステータスコードの設定
        $response->assertStatus(200);

        //コンテンツの確認
        $response->assertSee($medication->medication_name);
        $response->assertSee($medication->dosage);
        $response->assertSee($medication->notes);
        $response->assertSee($medication->effect);
        $response->assertSee($medication->side_effects);
    }


}