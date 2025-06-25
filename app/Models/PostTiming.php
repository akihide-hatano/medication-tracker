<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTiming extends Model
{
    use HasFactory;

    protected $table = 'posts_timing';

    public $incrementing = false;

    // マスアサインメント可能なカラム
    protected $fillable = [
        'post_id',
        'timing_tag_id',
        'is_completed',
    ];

    public $timestamps = true;

    public function post(){
        return $this->belongsTo(Post::class, 'post_id', 'post_id');
    }

    public function timingTag(){
        return $this->belongsTo(TimingTag::class, 'timing_tag_id', 'timing_tag_id');
    }
}
