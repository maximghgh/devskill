<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('final_tests', function (Blueprint $table) {
            $table->id();

            // связь с темой
            $table->foreignId('topic_id')
                ->constrained('topics')
                ->onDelete('cascade');

            // связь "chapter_id", в которую теперь будет записываться course_id
            // и чтобы не было FK-ошибок, делаем nullable и/или ссылаемся на courses
            $table->unsignedBigInteger('chapter_id')->nullable();
            // или с FK на courses:
            // $table->foreign('chapter_id')
            //       ->nullable()
            //       ->constrained('courses')
            //       ->onDelete('cascade');

            // JSON с блоками QuizTool
            $table->json('questions');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('final_tests', function (Blueprint $table) {
            // Откат: снимаем FK к courses...
            $table->dropForeign(['chapter_id']);

            // ...и возвращаем назад ссылку на chapters
            $table->foreign('chapter_id')
                  ->references('id')
                  ->on('chapters')
                  ->onDelete('cascade');
        });
    }
};
