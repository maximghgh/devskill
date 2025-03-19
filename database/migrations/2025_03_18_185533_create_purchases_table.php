<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('payment_method'); // 'card' или 'sbp'
            $table->json('payment_details')->nullable();
            $table->string('status')->default('pending'); // pending, completed, failed
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}
