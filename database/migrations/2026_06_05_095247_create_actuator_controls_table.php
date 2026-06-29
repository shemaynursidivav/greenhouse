<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('actuator_controls', function (Blueprint $table) {
            $table->id();
            $table->string('device_id');
            $table->string('actuator');
            $table->string('command');
            $table->string('value')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('actuator_controls');
    }
};