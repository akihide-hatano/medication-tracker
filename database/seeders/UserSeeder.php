<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Testing\Fakes\Fake;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('ja_JP');

        //10人のダミーを作成
        for( $i = 0; $i < 10; $i++){
            $email = "user{$i}@example.com";
            $name = $faker->name;

        //同じメールアドレスのユーザーが存在しない場合のみ作成
        if(User::where('email',$email)->doesntExist()){
            User::create([
                'name'=>$name,
                'email'=>$email,
                'email_verified_at' => now(),
                'password' => Hash::make('password'), // 全員共通のパスワード 'password'

            ]);
        }
        }
    }
}
