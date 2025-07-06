<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id('post_id');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // user_id（外部キー）
            $table->date('post_date');
            $table->boolean('all_meds_taken')->default(false); // 全ての薬を飲めたか（真偽値、デフォルトfalse）
            $table->text('reason_not_taken')->nullable(); // 飲めなかった理由（テキスト、NULL許容）
            $table->text('content')->nullable(); // 投稿内容（テキスト、NULL許容）
            $table->timestamps(); // created_at と updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};