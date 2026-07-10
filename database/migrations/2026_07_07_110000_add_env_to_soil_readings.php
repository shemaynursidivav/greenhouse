<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('soil_readings', function (Blueprint $t) {
            $t->float('temp_c')->nullable()->after('fan_speed');   // suhu udara
            $t->float('hum_pct')->nullable()->after('temp_c');     // kelembapan udara
            $t->float('lux')->nullable()->after('hum_pct');        // cahaya
        });
    }

    public function down(): void
    {
        Schema::table('soil_readings', function (Blueprint $t) {
            $t->dropColumn(['temp_c', 'hum_pct', 'lux']);
        });
    }
};