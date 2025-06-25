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
        Schema::create('timing_tags', function (Blueprint $table) {
            $table->id('timing_tag_id'); // 主キー (デフォルトのidメソッドでカスタム名も指定可能)
            $table->string('timing_name')->unique(); // タイミングの名前、ユニーク制約
            $table->timestamps(); // created_at と updated_at カラムを自動で追加
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timing_tags');
    }
};
