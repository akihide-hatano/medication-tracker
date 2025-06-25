<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicationTimingTag extends Model
{
    use HasFactory;

    public $table = 'medication_timing_tags';

    public $incrementing = false;

    protected $fillable = [
    'medication_id',
    'timing_tag_id',
    ];

    public function medication(){
        return $this->belongsTo(Medication::class,'medication_id','medication_id');
    }

    public function timingTag(){
        return $this->belongsTo(TimingTag::class, 'timing_tag_id', 'timing_tag_id');
    }
}
