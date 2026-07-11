<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SensorSync
{
    public function __construct(private GantryClient $gantry) {}

    private function num($v): ?float
    {
        if ($v === null) return null;
        $v = preg_replace('/[^0-9.\-]/', '', (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }

    private function setting(string $name): ?string
    {
        $r = DB::table('app_settings')->where('name', $name)->first();
        return $r->value ?? null;
    }

    private function fetch(?string $url): ?array
    {
        if (! $url) return null;
        try { return Http::timeout(2)->get($url)->throw()->json(); }
        catch (\Throwable $e) { return null; }
    }

    private function status($v, $min, $max): string
    {
        if ($min === null || $max === null) return 'normal';
        if ($v >= $min && $v <= $max) return 'normal';
        $dev = $v < $min
            ? ($min != 0 ? abs($v - $min) / abs($min) : 1)
            : ($max != 0 ? abs($v - $max) / abs($max) : 1);
        return $dev <= 0.15 ? 'warning' : 'danger';
    }

    public function run(): array
    {
        $values = [];

        if ($d = $this->fetch($this->setting('soil_url'))) {
            $values['soil_1']   = $this->num($d['s1']  ?? null);
            $values['soil_2']   = $this->num($d['s2']  ?? null);
            $values['soil_3']   = $this->num($d['s3']  ?? null);
            $values['soil_avg'] = $this->num($d['avg'] ?? null);
        }
        if ($d = $this->fetch($this->setting('env_url'))) {
           $values['temperature'] = $this->num($d['dht22']['suhu_C'] ?? $d['bme680']['suhu_C'] ?? $d['temp'] ?? $d['temperature'] ?? $d['suhu'] ?? null);
            $values['humidity']    = $this->num($d['dht22']['rh_pct'] ?? $d['bme680']['rh_pct'] ?? $d['hum'] ?? $d['humidity'] ?? $d['kelembaban'] ?? $d['kelembapan'] ?? null);
        }
        if ($d = $this->fetch($this->setting('light_url'))) {
            $values['light_intensity'] = $this->num($d['bh1750']['lux'] ?? $d['lux'] ?? $d['light'] ?? $d['intensity'] ?? null);
        }
        if ($d = $this->fetch($this->setting('fan_url'))) {
            $values['fan_speed'] = $this->num($d['fan']['speed_pct'] ?? $d['speed'] ?? $d['rpm'] ?? $d['pwm'] ?? null);
        }

        try {
            $s = $this->gantry->latestWithCaptures();
            if ($s) {
                $ripe = (int)($s['totalRipe'] ?? 0);
                $tot  = $ripe + (int)($s['totalTurning'] ?? 0) + (int)($s['totalUnripe'] ?? 0) + (int)($s['totalDamaged'] ?? 0);
                if ($tot > 0) $values['ripeness_score'] = round($ripe / $tot * 100, 1);
                if (isset($s['avgHeightCm']) && $s['avgHeightCm'] !== null) $values['plant_height'] = (float) $s['avgHeightCm'];
            }
        } catch (\Throwable $e) {}

        $written = 0;
        foreach (DB::table('sensors')->where('is_active', 1)->get() as $sen) {
            $t = $sen->sensor_type;
            if (! array_key_exists($t, $values) || $values[$t] === null) continue;

            $prev = DB::table('sensor_readings')
                ->where('sensor_type', $t)->where('device_id', $sen->device_id)
                ->orderByDesc('id')->value('status');

            $status = $this->status($values[$t], $sen->threshold_min, $sen->threshold_max);

            DB::table('sensor_readings')->insert([
                'device_id'   => $sen->device_id,
                'sensor_type' => $t,
                'value'       => $values[$t],
                'unit'        => $sen->unit,
                'status'      => $status,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
            $written++;

            if (in_array($status, ['warning', 'danger']) && $status !== $prev) {
                $this->sendAlert($sen, $values[$t], $status);
            }
        }

        return ['written' => $written, 'values' => $values];
    }

    /** Kirim email peringatan (hanya saat status berubah ke warning/danger). */
    private function sendAlert($sen, $value, $status): void
    {
        if ($this->setting('notify_enabled') !== '1') return;
        $to = $this->setting('notify_email');
        if (! $to) return;

        try {
            $lvl  = strtoupper($status);
            $body = "PERINGATAN STATUS SENSOR\n\n"
                  . "Sensor   : {$sen->label} ({$sen->sensor_type})\n"
                  . "Nilai PV : {$value} {$sen->unit}\n"
                  . "Setpoint : {$sen->threshold_min} - {$sen->threshold_max} {$sen->unit}\n"
                  . "Status   : {$lvl}\n"
                  . "Waktu    : " . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . " WIB\n\n"
                  . "-- Greenhouse Monitor";

            Mail::raw($body, function ($m) use ($to, $lvl, $sen) {
                $m->to($to)->subject("[{$lvl}] {$sen->label} - Greenhouse Monitor");
            });
        } catch (\Throwable $e) {
            Log::warning('notify mail gagal: ' . $e->getMessage());
        }
    }
}