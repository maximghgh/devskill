<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Путь к загруженному чеку/скриншоту оплаты (родитель прикрепляет после оплаты)
            $table->string('receipt_path')->nullable()->after('payment_details');
        });
    }

    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('receipt_path');
        });
    }
};
