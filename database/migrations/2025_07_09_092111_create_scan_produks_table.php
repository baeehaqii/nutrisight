<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('scan_produks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('nama_produk')->nullable();
            $table->string('jenis_produk')->nullable();
            $table->string('takaran_saji')->nullable();
            $table->string('grade_produk')->nullable();
            $table->date('tanggal_scan')->nullable();
            $table->integer('gula_per_saji')->nullable();
            $table->integer('gula_per_100ml')->nullable();
            $table->string('gambar_produk')->nullable();
            $table->text('rekomendasi_personalisasi')->nullable();
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
