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
    public function text_auth_user_create_medication():void{

        //ユーザーを作成し、ログイン状態にする。
        $user = User::factory()->create();
        $this->actingAs($user);
    }
}
