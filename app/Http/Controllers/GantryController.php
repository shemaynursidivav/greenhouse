<?php

namespace App\Http\Controllers;

use App\Services\GantryClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

class GantryController extends Controller
{
    public function __construct(private GantryClient $gantry) {}

    /** Halaman lama diarahkan ke halaman gabungan. */
    public function index()
    {
        return redirect()->route('gantry.live');
    }

    public function start(Request $r)
    {
        $data = $r->validate([
            'sessionType' => 'required|in:SCAN,WATERING,DATA_COLLECTION',
            'configId'    => 'nullable|integer',
        ]);
        try {
            $res = $this->gantry->start($data['sessionType'], $data['configId'] ?? null);
        } catch (\Throwable $e) {
            Log::error('Gantry start gagal: ' . $e->getMessage());
            return back()->with('gantry_error', 'Gagal memulai sesi. Pastikan RPi (Dafa) menyala & tidak ada sesi lain berjalan.');
        }
        return redirect()->route('gantry.live', ['id' => $res['sessionId'] ?? null]);
    }

    public function stop(int $id)
    {
        try {
            $this->gantry->stop($id);
        } catch (\Throwable $e) {
            Log::error('Gantry stop gagal: ' . $e->getMessage());
            return back()->with('gantry_error', 'Gagal menghentikan sesi.');
        }
        return back()->with('success', 'Sesi dihentikan.');
    }

    /** Halaman watch lama diarahkan ke halaman gabungan (sesi tertentu). */
    public function watch(int $id)
    {
        return redirect()->route('gantry.live', ['id' => $id]);
    }

    public function sensors()
    {
        return response()->json($this->gantry->rpiSensors());
    }

    /** Halaman gabungan: kontrol + hasil real-time + riwayat. */
    public function live(Request $r)
    {
        $id = $r->query('id');
        if (! $id) {
            try { $id = $this->gantry->latestId(); } catch (\Throwable $e) {}
        }
        return view('gantry.live', ['sessionId' => $id]);
    }

    public function liveData(Request $r)
    {
        $id = $r->query('id');
        $session = null;
        try {
            if (! $id) { $id = $this->gantry->latestId(); }
            if ($id)   { $session = $this->gantry->showLive((int) $id); }
        } catch (\Throwable $e) {
            Log::warning('Gantry liveData gagal: ' . $e->getMessage());
        }
        return response()->json(['session' => $session]);
    }

    /** Daftar sesi (untuk tabel riwayat di halaman gabungan). */
    public function sessionsJson()
    {
        $out = [];
        try {
            foreach ($this->gantry->list() as $s) {
                $out[] = [
                    'id'     => $s['id'] ?? null,
                    'type'   => $s['sessionType'] ?? '-',
                    'status' => $s['status'] ?? '-',
                    'plants' => $s['totalPlants'] ?? null,
                    'date'   => $this->fmtDate($s['startedAt'] ?? $s['createdAt'] ?? null),
                    'caps'   => ! empty($s['captures']),
                ];
            }
        } catch (\Throwable $e) {
            Log::warning('sessionsJson gagal: ' . $e->getMessage());
        }
        return response()->json(['sessions' => $out]);
    }

    public function image(Request $r)
    {
        $path = (string) $r->query('p', '');
        if (! Str::startsWith($path, '/api/uploads/')) abort(404);
        $resp = Http::withHeaders(['X-API-Key' => config('gantry.api_key')])
                    ->timeout(20)
                    ->get(rtrim(config('gantry.dashboard_url'), '/') . $path);
        if (! $resp->ok()) abort(404);
        return response($resp->body(), 200)
            ->header('Content-Type', $resp->header('Content-Type') ?: 'image/jpeg')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function recap()
    {
        $all = []; $error = null;
        try { $all = $this->gantry->list(); }
        catch (\Throwable $e) { $error = 'Data sesi belum bisa dimuat (pastikan tailnet & dashboard Dafa aktif).'; }

        $scans = []; $waters = [];
        foreach ($all as $s) {
            $type = $s['sessionType'] ?? ''; $status = $s['status'] ?? '';
            if ($type === 'SCAN' && $status === 'COMPLETED' && (int)($s['totalPlants'] ?? 0) > 0) {
                $ripe=(int)($s['totalRipe']??0); $turn=(int)($s['totalTurning']??0); $unripe=(int)($s['totalUnripe']??0); $broken=(int)($s['totalDamaged']??0);
                $total=$ripe+$turn+$unripe+$broken; $ready=0;
                if (! empty($s['harvestReadyIds'])) { $arr=json_decode($s['harvestReadyIds'],true); $ready=is_array($arr)?count($arr):0; }
                $scans[]=['id'=>$s['id']??'-','raw'=>$s['startedAt']??$s['createdAt']??'','date'=>$this->fmtDate($s['startedAt']??$s['createdAt']??null),
                    'plants'=>(int)($s['totalPlants']??0),'total'=>$total,'ripe'=>$ripe,'turning'=>$turn,'unripe'=>$unripe,'broken'=>$broken,'ready'=>$ready,
                    'pctRipe'=>$total>0?round($ripe/$total*100,1):0];
            } elseif ($type === 'WATERING' && $status === 'COMPLETED') {
                $waters[]=['id'=>$s['id']??'-','raw'=>$s['startedAt']??$s['createdAt']??'','date'=>$this->fmtDate($s['startedAt']??$s['createdAt']??null),
                    'stops'=>$s['stopsWatered']??0,'duration'=>$s['fuzzyDurationSec']??null,'mb'=>$s['moistureBeforeAvg']??null,'ma'=>$s['moistureAfterAvg']??null,'height'=>$s['maxHeightCm']??null];
            }
        }
        usort($scans, fn($a,$b)=>strcmp($b['raw'],$a['raw']));
        usort($waters, fn($a,$b)=>strcmp($b['raw'],$a['raw']));
        return view('gantry.recap', compact('scans','waters','error'));
    }

    private function fmtDate($iso)
    {
        if (! $iso) return '-';
        try { return Carbon::parse($iso)->timezone('Asia/Jakarta')->format('d/m/Y H:i'); }
        catch (\Throwable $e) { return (string) $iso; }
    }
}