<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scanning_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('scanning_sessions')->onDelete('cascade');
            $table->integer('nomor_tanaman'); // urutan tanaman ke-1, ke-2, dst
            $table->integer('baris');
            $table->integer('kolom');
            $table->float('ripeness_score')->nullable(); // 0-100%
            $table->string('kategori')->nullable(); // mentah/setengah_matang/matang
            $table->string('image_path')->nullable(); // path foto hasil scan
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scanning_results');
    }
};