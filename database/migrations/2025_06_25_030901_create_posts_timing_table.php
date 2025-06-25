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
        Schema::create('posts_timing', function (Blueprint $table) {
            // post_id カラム（外部キー）
            // この外部キーは 'posts' テーブルの 'post_id' を参照
            $table->foreignId('post_id')->constrained('posts', 'post_id')->onDelete('cascade');

            // timing_tag_id カラム（外部キー）
            // この外部キーは 'timing_tags' テーブルの 'timing_tag_id' を参照
            $table->foreignId('timing_tag_id')->constrained('timing_tags', 'timing_tag_id')->onDelete('cascade');

            // そのタイミングの薬が全て完了したかどうかのフラグ
            $table->boolean('is_completed')->default(false);

            // 複合プライマリキーの設定: post_id と timing_tag_id の組み合わせで一意にする
            // これにより、同じ投稿日かつ同じタイミングタグのレコードは重複しない
            $table->primary(['post_id', 'timing_tag_id']);

            // created_at と updated_at カラムを自動で追加
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts_timing');
    }
};
