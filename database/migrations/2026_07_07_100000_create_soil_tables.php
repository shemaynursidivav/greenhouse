<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soil_readings', function (Blueprint $t) {
            $t->id();
            $t->float('soil_1')->nullable();
            $t->float('soil_2')->nullable();
            $t->float('soil_3')->nullable();
            $t->float('soil_avg')->nullable();
            $t->float('fan_speed')->nullable();
            $t->json('raw')->nullable();
            $t->timestamp('created_at')->useCurrent();
            $t->index('created_at');
        });

        Schema::create('app_settings', function (Blueprint $t) {
            $t->string('name')->primary();   // 'name' (bukan 'key', karena reserved word)
            $t->text('value')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soil_readings');
        Schema::dropIfExists('app_settings');
    }
};