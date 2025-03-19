<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            // Ссылка на новость, к которой относится комментарий
            $table->foreignId('news_id')->constrained('news')->onDelete('cascade');
            // Родительский комментарий для вложенности (если NULL, значит комментарий первого уровня)
            $table->foreignId('parent_id')->nullable()->constrained('comments')->onDelete('cascade');
            // (Опционально) Ссылка на пользователя, оставившего комментарий
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->text('body');
            $table->timestamps();
            $table->string('user_name')->nullable()->after('body');
            $table->unsignedInteger('likes')->default(0);
            $table->unsignedInteger('dislikes')->default(0);
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}

