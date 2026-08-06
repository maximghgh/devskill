<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ответ набирается в визуальном редакторе, поэтому храним размеченный HTML.
     * Поле answer остаётся плоским текстом: по нему удобно искать
     * и его показывает список вопросов в админке.
     */
    public function up(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->text('answer_html')->nullable()->after('answer');
        });
    }

    public function down(): void
    {
        Schema::table('faqs', function (Blueprint $table) {
            $table->dropColumn('answer_html');
        });
    }
};
