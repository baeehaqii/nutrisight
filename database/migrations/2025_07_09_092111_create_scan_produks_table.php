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
        Schema::create('scan_produks', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk')->nullable();
            $table->string('jenis_produk')->nullable();
            $table->integer('total_gula')->nullable();
            $table->string('gambar_produk')->nullable();
            $table->date('tanggal_scan')->nullable();
            $table->text('rekomendasi')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_produks');
    }
};
