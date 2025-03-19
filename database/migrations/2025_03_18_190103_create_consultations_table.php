<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('email');
            $table->string('name');
            $table->string('phone');
            $table->string('status')->default('new'); // new, in_progress, completed
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultations');
    }

};
