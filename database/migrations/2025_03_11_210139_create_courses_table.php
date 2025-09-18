<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('card_title');
            $table->string('course_name');
            $table->decimal('price', 8, 2);
            $table->json('duration')->nullable();
            $table->text('description')->nullable();
            $table->integer('hours')->nullable();
            $table->integer('simulators')->nullable();
            $table->string('difficulty');
            $table->json('editor_data')->nullable();
            $table->json('teachers')->nullable();
            $table->json('language');
            $table->string('direction')->nullable();
            $table->integer('upgradequalification')->default(0);
            $table->string('card_image')->nullable();
            $table->string('description_image')->nullable();
            $table->string('pdf_path')->nullable();
            $table->date('start_date')->nullable()->index();
            $table->date('end_date')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('courses');
    }
}
