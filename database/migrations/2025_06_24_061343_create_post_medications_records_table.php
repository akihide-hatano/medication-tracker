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
            $table->id('record_id'); // PKを 'record_id' に指定

            // 外部キー: posts テーブルへの参照
            $table->foreignId('post_id')->constrained('posts', 'post_id')->onDelete('cascade');

            // 外部キー: medications テーブルへの参照
            $table->foreignId('medication_id')->constrained('medications', 'medication_id')->onDelete('cascade');

            // ★追加する外部キー: timing_tags テーブルへの参照
            $table->foreignId('timing_tag_id')->nullable()->constrained('timing_tags', 'timing_tag_id')->onDelete('set null');
            // nullable() と onDelete('set null') にすることで、タイミング情報がなくても記録可能にするか、
            // 必ず紐付ける場合は nullable() を外し onDelete('cascade') に変更

            $table->boolean('is_completed')->default(false); // 服用完了フラグ（真偽値、デフォルトfalse）
            $table->integer('taken_dosage')->nullable(); // 服用量（整数、null許容）
            $table->timestamp('taken_at')->nullable(); // 服用日時（タイムスタンプ、null許容）
            $table->text('reason_not_taken')->nullable(); // 服用しなかった理由（テキスト、null許容）

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