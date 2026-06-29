<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scanning_sessions', function (Blueprint $table) {
            $table->float('servo_pan')->nullable()->after('kolom');
            $table->float('servo_tilt')->nullable()->after('servo_pan');
            $table->string('stream_url')->nullable()->after('servo_tilt');
        });
    }

    public function down(): void
    {
        Schema::table('scanning_sessions', function (Blueprint $table) {
            $table->dropColumn(['servo_pan', 'servo_tilt', 'stream_url']);
        });
    }
};