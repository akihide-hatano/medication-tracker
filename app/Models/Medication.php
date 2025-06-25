<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medication extends Model
{
    use HasFactory;

    protected $primaryKey = 'medication_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable =[
        'medication_name',
        'dosage',
        'notes',
        'effect',
        'side_effects'
    ];

    public function timingTags(){
        return $this->belongsToMany(
        TimingTag::class,
        'medication_timing_tags',
        'medication_id',
        'timing_tag_id'
        );
    }
}
