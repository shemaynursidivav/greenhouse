<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensor_readings', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('sensor_type');
            $table->float('value');
            $table->string('unit');
            $table->string('status')->default('normal');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensor_readings');
    }
};