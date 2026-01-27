<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroupsTable extends Migration
{
    public function up()
    {
        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name_group');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->unsignedInteger('students_count')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('groups');
    }
}
