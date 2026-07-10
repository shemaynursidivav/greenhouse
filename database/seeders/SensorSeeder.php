<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Registrasi node sensor beserta setpoint (SPmin/SPmax).
 *
 * Dasar penetapan setpoint:
 *  - Suhu udara 25-30 C ......... Dinas Pertanian Buleleng (2024); Jurnal Eksakta (2025)
 *  - Kelembapan udara 60-80 % ... Jurnal Eksakta (2025)
 *  - Kelembapan tanah 60-80 % ... Jurnal Transistor (2018); ELKOMIKA (2022)
 *  - Kematangan cabai 0-60 % .... panen optimal saat 60-70 % buah matang
 *
 * Sensor tanpa setpoint (threshold null) ditampilkan sebagai data informatif,
 * tidak dievaluasi menjadi status Warning/Danger:
 *  - Intensitas cahaya : literatur menyatakan kebutuhan dalam durasi penyinaran
 *                        (8-10 jam/hari), bukan ambang lux sesaat.
 *  - Tinggi tanaman    : parameter pertumbuhan, bukan kondisi yang perlu diperingatkan.
 *  - Kecepatan fan     : keluaran aktuator, bukan kondisi lingkungan.
 *
 * Jalankan: php artisan db:seed --class=SensorSeeder
 */
class SensorSeeder extends Seeder
{
    public function run(): void
    {
        $sensors = [
            // sensor_type,      label,                 unit,  device_id,      min,  max
            ['temperature',      'Suhu Udara',          '°C',  'esp32_master', 25,   30],
            ['humidity',         'Kelembapan Udara',    '%',   'esp32_master', 60,   80],
            ['soil_1',           'Kelembapan Tanah 1',  '%',   'esp32_master', 60,   80],
            ['soil_2',           'Kelembapan Tanah 2',  '%',   'esp32_master', 60,   80],
            ['soil_3',           'Kelembapan Tanah 3',  '%',   'esp32_master', 60,   80],
            ['soil_avg',         'Rata-rata Tanah',     '%',   'esp32_master', 60,   80],
            ['ripeness_score',   'Kematangan Cabai',    '%',   'rpi_vision',    0,   60],

            // Tanpa setpoint -> selalu Normal, hanya ditampilkan sebagai data
            ['light_intensity',  'Intensitas Cahaya',   'lux', 'esp32_master', null, null],
            ['plant_height',     'Tinggi Tanaman',      'cm',  'esp32_master', null, null],
            ['fan_speed',        'Kecepatan Fan',       '%',   'esp32_master', null, null],
        ];

        foreach ($sensors as [$type, $label, $unit, $device, $min, $max]) {
            DB::table('sensors')->updateOrInsert(
                ['sensor_type' => $type, 'device_id' => $device],
                [
                    'label'         => $label,
                    'unit'          => $unit,
                    'owner'         => 'Admin',
                    'threshold_min' => $min,
                    'threshold_max' => $max,
                    'is_active'     => 1,
                    'updated_at'    => now(),
                    'created_at'    => now(),
                ]
            );
        }

        $this->command->info(count($sensors) . ' sensor terdaftar / diperbarui.');
    }
}