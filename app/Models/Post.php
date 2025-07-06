<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    // 主キーが 'post_id' であることを明示
    protected $primaryKey = 'post_id';

    // ★この行を追加してください★
    // 主キーが自動増分されることを明示的に指定
    public $incrementing = true;

    // マスアサインメント可能なカラム
    protected $fillable = [
        'user_id',
        'post_date',
        'content',          // ★ 'notes' を 'content' に変更 (もしDBカラムがcontentなら)
        'all_meds_taken',   // ★ 追加
        'reason_not_taken', // ★ 追加
    ];

    // 日付カラムはCarbonインスタンスとして扱われるようにキャスト
    protected $casts = [
        'post_date' => 'date',
        'all_meds_taken' => 'boolean',
    ];

    // リレーションシップの定義

    /**
     * この投稿が属するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id'); // Userモデルの主キーは通常'id'
    }

    /**
     * この投稿に関連する薬の服用記録を全て取得
     */
    public function postMedicationRecords()
    {
        return $this->hasMany(PostMedicationRecord::class, 'post_id', 'post_id');
    }

    /**
     * この投稿に関連する服用タイミングを取得（多対多）
     */
    public function timingTags()
    {
        return $this->belongsToMany(TimingTag::class, 'posts_timing', 'post_id', 'timing_tag_id')
                    ->withPivot('is_completed') // 中間テーブルのis_completedカラムも取得
                    ->withTimestamps(); // 中間テーブルのcreated_at/updated_atも取得
    }
}