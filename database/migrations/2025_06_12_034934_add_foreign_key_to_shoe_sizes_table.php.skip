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
        Schema::table('shoe_sizes', function (Blueprint $table) {
            // Menambahkan foreign key 'shoe_id' yang terhubung ke tabel 'shoes'
            $table->foreignId('shoe_id')->constrained('shoes')->onDelete('cascade');

            // Menambahkan kolom 'deleted_at' untuk fitur soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shoe_sizes', function (Blueprint $table) {
            // Menghapus constraint dan kolomnya jika migrasi di-rollback
            $table->dropForeign(['shoe_id']);
            $table->dropColumn('shoe_id');
            $table->dropSoftDeletes();
        });
    }
};
