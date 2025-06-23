<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Biarkan kosong, karena kolom sudah dibuat manual
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Kita bisa isi ini untuk berjaga-jaga jika perlu rollback
        Schema::table('product_transactions', function (Blueprint $table) {
            $table->dropColumn('size_id');
        });
    }
};
