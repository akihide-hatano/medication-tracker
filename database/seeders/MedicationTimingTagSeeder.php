<?php

namespace Database\Seeders;

use App\Models\Medication;
use App\Models\TimingTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use function mb_strpos; // マルチバイト文字対応のために必要

class MedicationTimingTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medications = Medication::all();
        $timingTags = TimingTag::all()->keyBy('timing_name');

        if ($medications->isEmpty()) {
            $this->command->info('MedicationSeeder が実行されていません。先に MedicationSeeder を実行してください。');
            return;
        }
        if ($timingTags->isEmpty()) {
            $this->command->info('TimingTagSeeder が実行されていません。先に TimingTagSeeder を実行してください。');
            return;
        }

        $medicationTimingMap = [
            'アムロジピン' => ['朝食後'],
            'メトグルコ' => ['朝食後', '夕食後'],
            'ロキソプロフェン' => ['頓服'],
            'ガスモチン' => ['朝食後'],
            'ビオフェルミン' => ['朝食後', '昼食後', '夕食後'],
            'プレドニン' => ['朝食後'],
            'セロクエル' => ['寝る前'],
            'アレグラ' => ['朝食後', '寝る前'],
            'ラシックス' => ['起床時'],
            'タケキャブ' => ['朝食前'],
            'バイアスピリン' => ['朝食後'],
            'クレストール' => ['夕食後'],
            'ワーファリン' => ['夕食後'],
            'ムコスタ' => ['夕食後'],
            'リリカ' => ['朝食後', '夕食後'],
            'デパス' => ['寝る前'],
            'セフジニル' => ['昼食後'],
            'カロナール' => ['頓服'],
            'ビタミンB群' => ['朝食後'],
            'マグミット' => ['寝る前'],
            'ダイアップ' => ['頓服'],
            'シングレア' => ['寝る前'],
            'ネキシウム' => ['朝食前'],
            'ミヤBM' => ['朝食後', '昼食後', '夕食後'],
            '葛根湯' => ['朝食前'],
            'リクシアナ' => ['夕食後'],
            'エパデール' => ['朝食後', '昼食後', '夕食後'],
            'ビソプロロール' => ['朝食後'],
            'ラベプラゾール' => ['朝食前'],
            'セレスタミン' => ['朝食後', '寝る前'],
            'テルネリン' => ['寝る前'],
            'トラムセット' => ['頓服'],
            'ザイロリック' => ['夕食後'],
            'アジルバ' => ['朝食後'],
            'フェロミア' => ['朝食後'],
            'レクサプロ' => ['朝食後'],
            'ムコダイン' => ['朝食後'],
            'シングリックス' => ['起床時'],
            'タミフル' => ['朝食後', '夕食後'],
            'リフレックス' => ['寝る前'],
            'ミカルディス' => ['朝食後'],
            'デノタス' => ['昼食後'],
            'ウルソ' => ['朝食後'],
            'ドグマチール' => ['朝食後', '夕食後'],
            'イブプロフェン' => ['頓服'],
            'リベルサス' => ['起床時'],
            'エビリファイ' => ['朝食後'],
            'カログラ' => ['朝食後', '寝る前'],
            'エディロール' => ['朝食後'],
            'アリセプト' => ['朝食後'],
            'サインバルタ' => ['朝食後'],
            'ガスター' => ['寝る前'],
            'ベタヒスチン' => ['朝食後', '昼食後', '夕食後'],
            'アモキシシリン' => ['朝食後'],
            'モンテルカスト' => ['寝る前'],
            'レバミピド' => ['朝食後'],
            'エチゾラム' => ['寝る前'],
            'フルイトラン' => ['起床時'],
            'ロゼレム' => ['寝る前'],
            'タクロリムス' => ['朝食後', '夕食後'],
        ];

        foreach ($medications as $medication) {
            $assigned = false;
            $currentMedicationTimingTagIds = []; // この薬に割り当てるタイミングタグIDを一時的に保持

            foreach ($medicationTimingMap as $keyword => $suggestedTimings) {
                if (mb_strpos($medication->medication_name, $keyword) !== false) {
                    foreach ($suggestedTimings as $timingName) {
                        $timingTag = $timingTags->get($timingName);
                        if ($timingTag) {
                            $currentMedicationTimingTagIds[] = $timingTag->timing_tag_id;
                        } else {
                            $this->command->warn("タイミングタグ '{$timingName}' が見つかりません。TimingTagSeeder を確認してください。");
                        }
                    }
                    $assigned = true;
                    break;
                }
            }

            // もし特定のキーワードにマッチしなかった薬であれば、ランダムに1つ割り当てる
            if (!$assigned) {
                if ($timingTags->isNotEmpty()) {
                    $randomTimingTag = $timingTags->random();
                    if ($randomTimingTag) {
                        $currentMedicationTimingTagIds[] = $randomTimingTag->timing_tag_id;
                    }
                }
            }
            // 収集したタイミングタグIDをまとめてアタッチ
            // syncWithoutDetaching は、指定したIDのみをアタッチし、既存のものはデタッチしない
            if (!empty($currentMedicationTimingTagIds)) {
                $medication->timingTags()->syncWithoutDetaching($currentMedicationTimingTagIds);
            }
        }
    }
}
