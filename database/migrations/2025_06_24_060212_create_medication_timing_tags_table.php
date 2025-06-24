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
        Schema::create('medication_timing_tags', function (Blueprint $table) {
            // medication_id カラム（外部キー）
            $table->foreignId('medication_id')->constrained('medications', 'medication_id')->onDelete('cascade');
            // timing_tag_id カラム（外部キー）
            $table->foreignId('timing_tag_id')->constrained('timing_tags', 'timing_tag_id')->onDelete('cascade');
            // 複合プライマリキーの設定
            $table->primary(['medication_id', 'timing_tag_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medication_timing_tags');
    }
};