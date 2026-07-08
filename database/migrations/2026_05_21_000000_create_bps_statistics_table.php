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
        Schema::create('bps_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('metric');            // e.g. 'tpt', 'gini_ratio', 'kemiskinan', etc.
            $table->string('tahun');             // e.g. '2024'
            $table->string('periode');           // e.g. 'Februari', 'Agustus', 'Maret', 'Tahunan', 'Januari', etc.
            $table->string('kode_wilayah');      // e.g. '1100' or '9999'
            $table->string('nama_wilayah');      // e.g. 'ACEH' or 'INDONESIA'
            $table->decimal('value', 14, 4)->nullable(); // The actual data value
            $table->string('sub_kategori')->nullable(); // For sub-categories (e.g., Perkotaan, Perdesaan)
            $table->timestamps();

            // Indexing for faster lookups
            $table->index(['metric', 'tahun', 'periode']);
            $table->index('kode_wilayah');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bps_statistics');
    }
};
