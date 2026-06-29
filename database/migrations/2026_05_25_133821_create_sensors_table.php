<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sensors', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('sensor_type');
            $table->string('label');
            $table->string('unit');
            $table->string('owner');
            $table->float('threshold_min')->nullable();
            $table->float('threshold_max')->nullable();
            $table->tinyInteger('is_active')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sensors');
    }
};