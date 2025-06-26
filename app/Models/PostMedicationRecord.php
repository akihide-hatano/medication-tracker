<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostMedicationRecord extends Model
{
    use HasFactory;

    protected $table = 'post_medications_records';
    protected $primaryKey = 'post_medication_record_id';
    public $incrementing = true;


    protected $keyType = 'int';

    protected $fillable = [
        'post_id',
        'medication_id',
        'timing_tag_id', // ★これがテーブルに直接存在するため、fillableに残す
        'is_completed',  // ★これがテーブルに直接存在するため、fillableに残す
        'taken_dosage',
        'taken_at',
        'reason_not_taken',
    ];

    // リレーションシップの定義
    /**
     * この服用記録が属する投稿（Post）を取得します。
     * 一対多（Postが親、PostMedicationRecordが子）のリレーションシップです。
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    /**
     * この服用記録が関連する薬（Medication）を取得します。
     * 一対多（Medicationが親、PostMedicationRecordが子）のリレーションシップです。
     */
    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id', 'medication_id');
    }

    // ★★★ここを修正：一対多のリレーションシップ 'timingTag' を定義★★★
    /**
     * この服用記録が関連する単一の服用タイミング（TimingTag）を取得します。
     * 一対多（TimingTagが親、PostMedicationRecordが子）のリレーションシップです。
     */
    public function timingTag() // ★単数形 'timingTag' に戻す
    {
        return $this->belongsTo(TimingTag::class, 'timing_tag_id', 'timing_tag_id');
    }
}