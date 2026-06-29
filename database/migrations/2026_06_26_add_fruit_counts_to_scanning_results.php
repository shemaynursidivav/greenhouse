<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scanning_results', function (Blueprint $table) {
            $table->integer('total_buah')->default(0)->after('image_path');
            $table->integer('count_ripe')->default(0)->after('total_buah');
            $table->integer('count_unripe')->default(0)->after('count_ripe');
            $table->integer('count_turning')->default(0)->after('count_unripe');
            $table->integer('count_broken')->default(0)->after('count_turning');
        });
    }

    public function down(): void
    {
        Schema::table('scanning_results', function (Blueprint $table) {
            $table->dropColumn(['total_buah', 'count_ripe', 'count_unripe', 'count_turning', 'count_broken']);
        });
    }
};