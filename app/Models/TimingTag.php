<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimingTag extends Model
{
    use HasFactory;

    protected $primaryKey = 'timing_tag_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable =[
        'timing_name',
    ];

    /**
     * このタイミングタグに関連付けられている薬を取得
     * (多対多リレーションシップ - medication_timing_tags 中間テーブルを使用)
     */
    public function medications()
    {
        return $this->belongsToMany(
            Medication::class,
            'medication_timing_tags', // 中間テーブル名
            'timing_tag_id',         // TimingTag モデルの外部キー
            'medication_id'          // Medication モデルの外部キー
        );
    }
    /**
     * このタイミングタグに関連付けられている投稿を取得
     * (多対多リレーションシップ - posts_timing 中間テーブルを使用)
     */
    public function posts()
    {
        return $this->belongsToMany(
            Post::class,
            'posts_timing', // 中間テーブル名
            'timing_tag_id', // TimingTag モデルの外部キー
            'post_id'       // Post モデルの外部キー
        )->withPivot('is_completed'); // is_completed カラムも取得できるようにする
    }
}
