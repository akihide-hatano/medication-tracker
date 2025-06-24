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
            // medication_id カラム（外部キー）
            $table->foreignId('post_id')->constrained('posts', 'post_id')->onDelete('cascade');
            // timing_tag_id カラム（外部キー）
            $table->foreignId('timing_tag_id')->constrained('timing_tags', 'timing_tag_id')->onDelete('cascade');
            // 複合プライマリキーの設定
            $table->primary(['post_id', 'timing_tag_id']);
            // そのタイミングの薬が全て完了したかどうかのフラグ
            $table->boolean('is_completed')->default(false);

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
