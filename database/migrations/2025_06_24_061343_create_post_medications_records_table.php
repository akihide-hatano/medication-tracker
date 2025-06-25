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
        Schema::create('post_medications_records', function (Blueprint $table) {
            $table->id('post_medication_record_id'); // 主キー

            // 外部キー: posts テーブルへの参照
            $table->foreignId('post_id')->constrained('posts', 'post_id')->onDelete('cascade');

            // 外部キー: medications テーブルへの参照
            $table->foreignId('medication_id')->constrained('medications', 'medication_id')->onDelete('cascade');

            // 外部キー: timing_tags テーブルへの参照
            $table->foreignId('timing_tag_id')->constrained('timing_tags', 'timing_tag_id')->onDelete('cascade');

            $table->boolean('is_completed')->default(false); // 服用したかどうか
            $table->string('taken_dosage')->nullable();      // 実際に服用した量（例: '1錠'）
            $table->timestamp('taken_at')->nullable();       // 実際に服用した日時
            $table->text('reason_not_taken')->nullable();    // 服用しなかった理由

            // 複合ユニークキー: 同じ投稿、同じ薬、同じタイミングの記録は一つのみ
            $table->unique(['post_id', 'medication_id', 'timing_tag_id'], 'post_med_timing_unique');

            $table->timestamps(); // created_at と updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_medications_records');
    }
};