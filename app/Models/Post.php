<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasFactory;

    protected $primaryKey = 'post_id';
    public $incrementing = true; // PKが自動増分の場合 (デフォルトでtrueですが、明示的に)
    protected $keyType = 'int'; // PKの型 (デフォルトで'int'ですが、明示的に)

    protected $fillable = [
        'user_id',
        'post_date',
        'all_meds_taken',
        'reason_not_taken',
        'content',
    ];

    /**
     * この投稿が属するユーザーを取得
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * この投稿に関連付けられているタイミングタグを取得
     * (多対多リレーションシップ - posts_timing 中間テーブルを使用)
     */
    public function timingTags()
    {
        return $this->belongsToMany(
            TimingTag::class,
            'posts_timing', // 中間テーブル名
            'post_id',      // Post モデルの外部キー
            'timing_tag_id' // TimingTag モデルの外部キー
        )->withPivot('is_completed'); // is_completed カラムも取得できるようにする
    }
}
