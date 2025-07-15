<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('final_test_results', function (Blueprint $table) {
            $table->id();

            // кто и к какому курсу / тесту относится
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            //  если тест привязан к главе или таблице final_tests, замените course_id
            //  $table->foreignId('final_test_id')->constrained()->cascadeOnDelete();

            // сами ответы и статистика
            $table->json('answers'); 
            $table->unsignedTinyInteger('score');   
            $table->unsignedSmallInteger('correct_count');
            $table->unsignedSmallInteger('total_questions');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('final_test_results');
    }
};

