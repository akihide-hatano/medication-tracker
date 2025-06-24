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
        $timingTags =[
            '朝食前',
            '朝食後',
            '昼食前',
            '昼食後',
            '夕食前',
            '夕食後',
            '寝る前',
            '頓服',
            '起床時',
        ];

        foreach($timingTags as $tagName){
            if(TimingTag::where('timing_name',$tagName)){
                TimingTag::create([
                    'timing_name'=>$tagName,
                ]);
            }
        }
    }
}
