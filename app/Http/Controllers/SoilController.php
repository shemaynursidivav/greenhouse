<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SoilController extends Controller
{
    private function getSetting(string $name): ?string
    {
        $row = DB::table('app_settings')->where('name', $name)->first();
        return $row->value ?? null;
    }

    /** Buang unit/teks (%, C, lux, spasi), ambil angkanya. "90.0%" -> 90.0 */
    private function num($v): ?float
    {
        if ($v === null) return null;
        $v = preg_replace('/[^0-9.\-]/', '', (string) $v);
        return is_numeric($v) ? (float) $v : null;
    }

    private function fetch(?string $url): ?array
    {
        if (! $url) return null;
        try {
            return Http::timeout(4)->get($url)->throw()->json();
        } catch (\Throwable $e) {
            Log::warning('rio fetch gagal: ' . $e->getMessage());
            return null;
        }
    }

    public function index()
    {
        return view('soil', [
            'soilUrl'  => $this->getSetting('soil_url'),
            'envUrl'   => $this->getSetting('env_url'),
            'lightUrl' => $this->getSetting('light_url'),
            'fanUrl'   => $this->getSetting('fan_url'),
            'energyUrl'=> $this->getSetting('energy_url'),
        ]);
    }

    public function saveUrl(Request $r)
    {
        $r->validate([
            'soil_url'  => 'nullable|url',
            'env_url'   => 'nullable|url',
            'light_url' => 'nullable|url',
            'fan_url'   => 'nullable|url',
            'energy_url'=> 'nullable|url',
        ]);
        foreach (['soil_url', 'env_url', 'light_url', 'fan_url', 'energy_url'] as $k) {
            DB::table('app_settings')->updateOrInsert(['name' => $k], ['value' => $r->input($k)]);
        }

        return back()->with('success', 'URL disimpan.');
    }

    /** Ambil semua sensor Rio sekali jalan, simpan 1 baris, balikan JSON. Dipanggil tiap 1 detik. */
    public function poll()
    {
        $out = ['at' => now()->format('H:i:s')];
        $row = ['created_at' => now()];
        $any = false;

        // --- Kelembapan tanah: {"s1":"90.0%","s2":..,"s3":..,"avg":..,"kategori":".."} ---
        if ($d = $this->fetch($this->getSetting('soil_url'))) {
            $s1 = $this->num($d['s1'] ?? null);
            $s2 = $this->num($d['s2'] ?? null);
            $s3 = $this->num($d['s3'] ?? null);
            $avg = $this->num($d['avg'] ?? null);
            if ($avg === null) { $v = array_filter([$s1,$s2,$s3], fn($x)=>$x!==null); $avg = count($v)?round(array_sum($v)/count($v),2):null; }
            $row += ['soil_1'=>$s1,'soil_2'=>$s2,'soil_3'=>$s3,'soil_avg'=>$avg];
            $out += ['soil_1'=>$s1,'soil_2'=>$s2,'soil_3'=>$s3,'soil_avg'=>$avg,'kategori'=>$d['kategori']??null];
            $any = true;
        }

        // --- Suhu + kelembapan udara (1 API Rio). GANTI field kalau beda. ---
        if ($d = $this->fetch($this->getSetting('env_url'))) {
           $temp = $this->num($d['dht22']['suhu_C'] ?? $d['bme680']['suhu_C'] ?? $d['temp'] ?? $d['temperature'] ?? $d['suhu'] ?? null);
            $hum  = $this->num($d['dht22']['rh_pct'] ?? $d['bme680']['rh_pct'] ?? $d['hum'] ?? $d['humidity'] ?? $d['kelembaban'] ?? $d['kelembapan'] ?? null);
            $row += ['temp_c'=>$temp,'hum_pct'=>$hum];
            $out += ['temp_c'=>$temp,'hum_pct'=>$hum];
            $any = true;
        }

        // --- Cahaya (BH1750). GANTI field kalau beda. ---
      if ($d = $this->fetch($this->getSetting('light_url'))) {
            $lux = $this->num($d['bh1750']['lux'] ?? $d['lux'] ?? $d['light'] ?? $d['intensity'] ?? null);
            $row += ['lux'=>$lux];
            $out += ['lux'=>$lux];
            $any = true;
        }

        // --- Fan. GANTI field kalau beda. ---
        if ($d = $this->fetch($this->getSetting('fan_url'))) {
            $fan = $this->num($d['fan']['speed_pct'] ?? $d['speed'] ?? $d['rpm'] ?? $d['pwm'] ?? null);
            $row += ['fan_speed'=>$fan];
            $out += ['fan_speed'=>$fan];
            $any = true;
        }

        // --- Energi (PZEM): {"ac":{voltage_V,current_A,power_W,pf,freq_Hz},"total":{power_W,dc_power_W}} ---
        if ($d = $this->fetch($this->getSetting('energy_url'))) {
            $volt = $this->num($d['ac']['voltage_V']  ?? null);
            $curr = $this->num($d['ac']['current_A']  ?? null);
            $pwr  = $this->num($d['ac']['power_W']    ?? null);
            $pf   = $this->num($d['ac']['pf']         ?? null);
            $freq = $this->num($d['ac']['freq_Hz']    ?? null);
            $tot  = $this->num($d['total']['power_W'] ?? null);
            $dc   = $this->num($d['total']['dc_power_W'] ?? null);
            $row += ['volt_v'=>$volt,'curr_a'=>$curr,'power_w'=>$pwr,'total_w'=>$tot,'pf'=>$pf];
            $out += ['volt_v'=>$volt,'curr_a'=>$curr,'power_w'=>$pwr,'pf'=>$pf,'freq_hz'=>$freq,'total_w'=>$tot,'dc_w'=>$dc];
            $any = true;
        }

        if (! $any) {
            return response()->json(['error' => 'Belum ada URL yang bisa diambil. Isi & simpan URL dulu, pastikan 1 jaringan dgn Rio.'], 400);
        }

        $row['raw'] = json_encode($out);
        DB::table('soil_readings')->insert($row);

        return response()->json($out);
    }

    public function history(Request $r)
    {
        $n = min((int) $r->query('n', 120), 1000);

        $rows = DB::table('soil_readings')
            ->orderByDesc('id')->limit($n)
            ->get(['soil_1','soil_2','soil_3','soil_avg','temp_c','hum_pct','lux','fan_speed','volt_v','curr_a','power_w','total_w','pf','created_at'])
            ->reverse()->values();

        return response()->json($rows);
    }
}