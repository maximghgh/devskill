<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('course_comments', function (Blueprint $table) {
            $table->id();
            // Внешний ключ для связи с курсом
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            // Если комментарии привязаны к пользователям (можно сделать nullable для анонимных)
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            // Текст комментария
            $table->text('body');
            // Для вложенных комментариев: parent_id, если null – комментарий верхнего уровня
            $table->foreignId('parent_id')->nullable()->constrained('course_comments')->onDelete('cascade');
            // Можно добавить поля для лайков, дизлайков и т.д.
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('dislikes')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_comments');
    }
}
