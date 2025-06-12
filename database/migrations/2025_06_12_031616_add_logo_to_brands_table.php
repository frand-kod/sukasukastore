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
        Schema::table('brands', function (Blueprint $table) {
            // Ini akan menambahkan kolom 'logo' dengan tipe VARCHAR (string)
            // ->nullable() penting agar data lama yang tidak punya logo tidak error
            // ->after('name') adalah opsional, hanya untuk menempatkan kolomnya setelah kolom 'name' agar rapi
            $table->string('logo')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            // Ini akan menghapus kolom 'logo' jika migrasi di-rollback
            $table->dropColumn('logo');
        });
    }
};
