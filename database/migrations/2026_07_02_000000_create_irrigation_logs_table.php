<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('irrigation_logs', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');

            // Input mentah dari sensor
            $table->float('soil_zona_1')->nullable();
            $table->float('soil_zona_2')->nullable();
            $table->float('soil_zona_3')->nullable();
            $table->float('tinggi_tanaman')->nullable();

            // Hasil interpretasi fuzzy (label)
            $table->string('fase_tanaman')->nullable();       // Semai / Vegetatif Awal / dst
            $table->string('kondisi_kelembapan')->nullable(); // Sangat Kering / Kering / dst

            // Output fuzzy
            $table->integer('durasi_irigasi')->default(0);    // 0 / 30 / 60 / 90 / 120 detik
            $table->string('solenoid_status')->default('close'); // open / close

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('irrigation_logs');
    }
};