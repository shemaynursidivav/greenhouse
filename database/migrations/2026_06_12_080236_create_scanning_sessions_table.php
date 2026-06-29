<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanning_sessions', function (Blueprint $table) {
            $table->id();
            $table->integer('jumlah_tanaman');
            $table->float('jarak_antar_tanaman'); // cm
            $table->float('jarak_frame_ke_tanaman'); // cm
            $table->string('susunan_tanaman'); // contoh: "3x8" atau "2x7"
            $table->integer('baris');
            $table->integer('kolom');
            $table->boolean('penyiraman')->default(false);
            $table->string('status')->default('pending');
            // pending = belum mulai
            // scanning = sedang berjalan
            // done = selesai
            $table->integer('progress')->default(0); // 0-100%
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanning_sessions');
    }
};      