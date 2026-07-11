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
        Schema::table('soil_readings', function (Blueprint $t) {
            $t->float('volt_v')->nullable()->after('lux');
            $t->float('curr_a')->nullable()->after('volt_v');
            $t->float('power_w')->nullable()->after('curr_a');
            $t->float('total_w')->nullable()->after('power_w');
            $t->float('pf')->nullable()->after('total_w');
        });
    }

    public function down(): void
    {
        Schema::table('soil_readings', function (Blueprint $t) {
            $t->dropColumn(['volt_v', 'curr_a', 'power_w', 'total_w', 'pf']);
        });
    }
    };