<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GantryClient
{
    private function http()
    {
        return Http::withHeaders([
                'X-API-Key' => config('gantry.api_key'),
            ])
            ->baseUrl(config('gantry.dashboard_url'))
            ->timeout(30);
    }

    public function start(string $type = 'SCAN', ?int $configId = null, ?int $bedId = null): array
    {
        $bedId = $bedId ?? (int) config('gantry.bed_id');

        $created = $this->http()->post('/api/partner/sessions', [
                'sessionType' => $type,
                'bedId'       => $bedId,
                'configId'    => $configId,
            ])->throw()->json();

        $sessionId = $created['sessionId'] ?? null;

        if ($sessionId) {
            $this->http()
                ->post("/api/partner/sessions/{$sessionId}/start")
                ->throw();
        }

        return $created;
    }

    public function stop(int $id): array
    {
        return $this->http()
            ->post("/api/partner/sessions/{$id}/stop")
            ->throw()->json();
    }

    public function list(): array
    {
        return $this->http()
            ->get('/api/partner/sessions')
            ->throw()->json('sessions');
    }

    public function show(int $id): array
    {
        return $this->http()
            ->get("/api/partner/sessions/{$id}")
            ->throw()->json('session');
    }

    /** Ambil 1 session untuk polling live — timeout pendek biar tidak 504. */
    public function showLive(int $id): ?array
    {
        return Http::withHeaders(['X-API-Key' => config('gantry.api_key')])
            ->baseUrl(config('gantry.dashboard_url'))
            ->timeout(8)
            ->get("/api/partner/sessions/{$id}")
            ->throw()->json('session');
    }

    /** Sesi terbaru yang punya capture (untuk halaman monitoring terkini). */
    public function latestWithCaptures(): ?array
    {
        $sessions = $this->list();

        foreach ($sessions as $s) {
            if (! empty($s['captures'])) {
                return $s;
            }
        }

        return $sessions[0] ?? null;
    }

    /** Id sesi terbaru yang ada capture-nya (dipakai halaman live). */
    public function latestId(): ?int
    {
        $s = $this->latestWithCaptures();
        return $s['id'] ?? null;
    }

    public function eventsUrl(int $sessionId): string
    {
        return rtrim(config('gantry.rpi_url'), '/') . "/sessions/{$sessionId}/events";
    }

    // Sensor langsung dari RPi Dafa (port 8000)

    private function rpi()
    {
        return Http::baseUrl(rtrim(config('gantry.rpi_url'), '/'))->timeout(10);
    }

    public function rpiSensors(): array
    {
        $env = $soil = $light = null;

        try { $env   = $this->rpi()->get('/sensors/environment')->json(); } catch (\Throwable $e) {}
        try { $soil  = $this->rpi()->get('/sensors/soil')->json(); }        catch (\Throwable $e) {}
        try { $light = $this->rpi()->get('/sensors/light')->json(); }       catch (\Throwable $e) {}

        return [
            'temperature_c' => $env['temperature_c']         ?? null,
            'humidity_pct'  => $env['humidity_pct']          ?? null,
            'fan_speed_pct' => $env['exhaust_fan_speed_pct'] ?? null,
            'lux'           => $light['lux']                 ?? null,
            'soil'          => $soil['sensors'] ?? [],
            'online'        => $env !== null,
        ];
    }
}