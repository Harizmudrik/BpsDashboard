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
        Schema::create('tpt_statistics', function (Blueprint $table) {
            $table->id();
            $table->string('tahun');           // e.g. "2025"
            $table->string('periode');          // "Februari" or "Agustus"
            $table->string('kode_wilayah');     // e.g. "1100"
            $table->string('nama_wilayah');     // e.g. "ACEH"
            $table->decimal('tpt_value', 8, 2)->nullable(); // TPT percentage
            $table->timestamps();

            $table->unique(['tahun', 'periode', 'kode_wilayah']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tpt_statistics');
    }
};
