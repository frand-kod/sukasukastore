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
        Schema::table('shoe_photos', function (Blueprint $table) {
            // Menambahkan foreign key 'shoe_id' yang terhubung ke tabel 'shoes'
            // onDelete('cascade') berarti jika sebuah sepatu dihapus, semua fotonya juga akan ikut terhapus.
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
        Schema::table('shoe_photos', function (Blueprint $table) {
            // Urutan penghapusan penting: hapus constraint dulu, baru kolomnya
            $table->dropForeign(['shoe_id']);
            $table->dropColumn('shoe_id');
            $table->dropSoftDeletes();
        });
    }
};
