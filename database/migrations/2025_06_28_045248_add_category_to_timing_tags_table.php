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
        Schema::table('timing_tags', function (Blueprint $table) {
            // 大カテゴリ名を追加（例: '朝', '昼', '夕', '寝る前', 'その他'）
            $table->string('category_name')->nullable()->after('timing_name');

            // 大カテゴリの表示順序を追加
            $table->integer('category_order')->nullable()->after('category_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('timing_tags', function (Blueprint $table) {
            // ロールバック時にカラムを削除
            $table->dropColumn('category_name');
            $table->dropColumn('category_order');
        });
    }
};

