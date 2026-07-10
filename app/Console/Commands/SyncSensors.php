<?php

namespace App\Console\Commands;

use App\Services\SensorSync;
use Illuminate\Console\Command;

class SyncSensors extends Command
{
    /** Dipanggil: php artisan sensors:sync */
    protected $signature = 'sensors:sync';

    protected $description = 'Tarik data node sensor & subsistem gantry, simpan ke database, kirim notifikasi bila status berubah.';

    public function handle(SensorSync $sync): int
    {
        try {
            $r = $sync->run();
            $this->info('[' . now()->timezone('Asia/Jakarta')->format('H:i:s') . '] '
                . $r['written'] . ' pembacaan tersimpan.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Sync gagal: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}