<?php

namespace Database\Seeders;

use App\Models\TimingTag;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimingTagSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 服薬タイミングとそれに対応するカテゴリ名、カテゴリ順序を定義
        $timingTagsData = [
            // timing_tag_id は自動インクリメントに任せるか、必要なら手動で設定
            // 重要なのは timing_name のユニーク性と、category_name/category_orderの紐付け
            ['timing_name' => '起床時', 'category_name' => '朝', 'category_order' => 1], // 朝食前よりも優先順位が高い場合
            ['timing_name' => '朝食前', 'category_name' => '朝', 'category_order' => 2],
            ['timing_name' => '朝食後', 'category_name' => '朝', 'category_order' => 3],
            ['timing_name' => '昼食前', 'category_name' => '昼', 'category_order' => 4],
            ['timing_name' => '昼食後', 'category_name' => '昼', 'category_order' => 5],
            ['timing_name' => '夕食前', 'category_name' => '夕', 'category_order' => 6],
            ['timing_name' => '夕食後', 'category_name' => '夕', 'category_order' => 7],
            ['timing_name' => '寝る前', 'category_name' => '寝る前', 'category_order' => 8],
            ['timing_name' => '頓服', 'category_name' => '頓服', 'category_order' => 10], // 頓服はカテゴリとして独立させ、一番最後に表示されるように大きい値を設定
            ['timing_name' => 'その他', 'category_name' => 'その他', 'category_order' => 100], // その他もカテゴリとして独立させ、最後に表示されるように大きい値を設定
        ];

        foreach ($timingTagsData as $data) {
            // firstOrCreate を使用して、重複を防ぎつつ新しいカラムも設定
            TimingTag::firstOrCreate(
                ['timing_name' => $data['timing_name']], // このキーでレコードを検索
                [
                    'category_name' => $data['category_name'],
                    'category_order' => $data['category_order'],
                ]
            );
        }
    }
}