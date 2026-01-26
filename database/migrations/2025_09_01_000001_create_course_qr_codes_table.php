<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseQrCodesTable extends Migration
{
    public function up()
    {
        Schema::create('course_qr_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('qr_code_id')->constrained('qr_codes')->onDelete('cascade');
            $table->timestamps();

            $table->unique('course_id');
            $table->unique('qr_code_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('course_qr_codes');
    }
}
