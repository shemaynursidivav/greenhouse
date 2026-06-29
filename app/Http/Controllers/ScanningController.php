<?php

namespace App\Http\Controllers;

use App\Models\ScanningSession;
use App\Models\ScanningResult;
use Illuminate\Http\Request;

class ScanningController extends Controller
{
    // ─────────────────────────────────────────
    // HALAMAN WEB
    // ─────────────────────────────────────────

    public function index()
    {
        $sessions = ScanningSession::orderBy('created_at', 'desc')->take(10)->get();
        return view('scanning', compact('sessions'));
    }

    public function liveView($id)
    {
        $session = ScanningSession::findOrFail($id);
        $results = ScanningResult::where('session_id', $id)
            ->orderBy('nomor_tanaman')
            ->get();

        $rekap      = $this->hitungRekap($results);
        $rekapTotal = $this->hitungRekapTotal();

        return view('scanning-live', compact('session', 'results', 'rekap', 'rekapTotal'));
    }

    // ─────────────────────────────────────────
    // API — SESSION MANAGEMENT
    // ─────────────────────────────────────────

    public function createSession(Request $request)
    {
        $request->validate([
            'jumlah_tanaman'         => 'required|integer|min:1',
            'jarak_antar_tanaman'    => 'required|numeric',
            'jarak_frame_ke_tanaman' => 'required|numeric',
            'susunan_tanaman'        => 'required|string',
            'baris'                  => 'required|integer',
            'kolom'                  => 'required|integer',
            'servo_pan'              => 'nullable|numeric',
            'servo_tilt'             => 'nullable|numeric',
            'penyiraman'             => 'nullable|boolean',
        ]);

        $session = ScanningSession::create([
            'jumlah_tanaman'         => $request->jumlah_tanaman,
            'jarak_antar_tanaman'    => $request->jarak_antar_tanaman,
            'jarak_frame_ke_tanaman' => $request->jarak_frame_ke_tanaman,
            'susunan_tanaman'        => $request->susunan_tanaman,
            'baris'                  => $request->baris,
            'kolom'                  => $request->kolom,
            'servo_pan'              => $request->servo_pan,
            'servo_tilt'             => $request->servo_tilt,
            'penyiraman'             => $request->boolean('penyiraman'),
            'status'                 => 'pending',
            'progress'               => 0,
        ]);

        return response()->json([
            'message'    => 'Sesi scanning dibuat',
            'session_id' => $session->id,
            'session'    => $session,
        ], 201);
    }

    public function createPenyiraman(Request $request)
    {
        $request->validate([
            'jumlah_tanaman'         => 'required|integer|min:1',
            'jarak_antar_tanaman'    => 'required|numeric',
            'jarak_frame_ke_tanaman' => 'required|numeric',
            'susunan_tanaman'        => 'required|string',
            'baris'                  => 'required|integer',
            'kolom'                  => 'required|integer',
        ]);

        $session = ScanningSession::create([
            'jumlah_tanaman'         => $request->jumlah_tanaman,
            'jarak_antar_tanaman'    => $request->jarak_antar_tanaman,
            'jarak_frame_ke_tanaman' => $request->jarak_frame_ke_tanaman,
            'susunan_tanaman'        => $request->susunan_tanaman,
            'baris'                  => $request->baris,
            'kolom'                  => $request->kolom,
            'servo_pan'              => null,
            'servo_tilt'             => null,
            'penyiraman'             => true,
            'status'                 => 'pending',
            'progress'               => 0,
        ]);

        return response()->json([
            'message'    => 'Sesi penyiraman dibuat',
            'session_id' => $session->id,
            'session'    => $session,
        ], 201);
    }

    // ─────────────────────────────────────────
    // API — UNTUK DAFA (Raspberry Pi)
    // ─────────────────────────────────────────

    public function getActiveSession()
    {
        $session = ScanningSession::where('status', 'pending')
            ->orderBy('created_at', 'asc')
            ->first();

        if (!$session) {
            return response()->json(['message' => 'Tidak ada sesi pending'], 404);
        }

        return response()->json($session);
    }

    public function updateStreamUrl(Request $request, $id)
    {
        $request->validate([
            'stream_url' => 'required|string',
        ]);

        $session = ScanningSession::findOrFail($id);
        $session->stream_url = $request->stream_url;
        $session->save();

        try {
            broadcast(new \App\Events\ScanningProgress($session));
        } catch (\Exception $e) {
            logger('Pusher broadcast gagal: ' . $e->getMessage());
        }

        return response()->json([
            'message'    => 'Stream URL diterima',
            'session_id' => $session->id,
            'stream_url' => $session->stream_url,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,scanning,done,error',
        ]);

        $session = ScanningSession::findOrFail($id);
        $session->status = $request->status;

        if ($request->status === 'scanning' && !$session->started_at) {
            $session->started_at = now();
        }

        if (in_array($request->status, ['done', 'error'])) {
            $session->finished_at = now();
            if ($request->status === 'done') {
                $session->progress = 100;
            }
        }

        $session->save();

        try {
            broadcast(new \App\Events\ScanningProgress($session));
        } catch (\Exception $e) {
            logger('Pusher broadcast gagal: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Status diupdate ke: ' . $request->status,
            'session' => $session,
        ]);
    }

    public function updateProgress(Request $request, $id)
    {
        $request->validate([
            'progress' => 'required|integer|min:0|max:100',
        ]);

        $session = ScanningSession::findOrFail($id);
        $session->progress = $request->progress;
        $session->save();

        try {
            broadcast(new \App\Events\ScanningProgress($session));
        } catch (\Exception $e) {
            logger('Pusher broadcast gagal: ' . $e->getMessage());
        }

        return response()->json([
            'message'  => 'Progress diupdate',
            'progress' => $session->progress,
        ]);
    }

    public function submitResult(Request $request, $id)
    {
        $request->validate([
            'nomor_tanaman'  => 'required|integer',
            'baris'          => 'required|integer',
            'kolom'          => 'required|integer',
            'ripeness_score' => 'nullable|numeric',
            'kategori'       => 'nullable|string',
            'image_path'     => 'nullable|string',
            'total_buah'     => 'nullable|integer',
            'count_ripe'     => 'nullable|integer',
            'count_unripe'   => 'nullable|integer',
            'count_turning'  => 'nullable|integer',
            'count_broken'   => 'nullable|integer',
        ]);

        $result = ScanningResult::updateOrCreate(
            [
                'session_id'    => $id,
                'nomor_tanaman' => $request->nomor_tanaman,
            ],
            [
                'baris'          => $request->baris,
                'kolom'          => $request->kolom,
                'ripeness_score' => $request->ripeness_score,
                'kategori'       => $request->kategori,
                'image_path'     => $request->image_path,
                'total_buah'     => $request->total_buah    ?? 0,
                'count_ripe'     => $request->count_ripe    ?? 0,
                'count_unripe'   => $request->count_unripe  ?? 0,
                'count_turning'  => $request->count_turning ?? 0,
                'count_broken'   => $request->count_broken  ?? 0,
            ]
        );

        $session      = ScanningSession::findOrFail($id);
        $totalScanned = ScanningResult::where('session_id', $id)->count();
        $progress     = round(($totalScanned / $session->jumlah_tanaman) * 100);
        $session->progress = min($progress, 99);
        $session->save();

        $allResults = ScanningResult::where('session_id', $id)->get();
        $rekap      = $this->hitungRekap($allResults);

        try {
            broadcast(new \App\Events\ScanningProgress($session, $rekap));
        } catch (\Exception $e) {
            logger('Pusher broadcast gagal: ' . $e->getMessage());
        }

        return response()->json([
            'message'  => 'Hasil scan disimpan',
            'result'   => $result,
            'progress' => $session->progress,
            'rekap'    => $rekap,
        ], 201);
    }

    // ─────────────────────────────────────────
    // API — REKAPAN
    // ─────────────────────────────────────────

    public function getResults($id)
    {
        $session = ScanningSession::findOrFail($id);
        $results = ScanningResult::where('session_id', $id)
            ->orderBy('nomor_tanaman')
            ->get();

        $rekap = $this->hitungRekap($results);

        return response()->json([
            'session'     => $session,
            'results'     => $results,
            'rekap'       => $rekap,
            'rekap_total' => $this->hitungRekapTotal(),
        ]);
    }

    public function getRekap()
    {
        $allResults = ScanningResult::all();
        $rekap      = $this->hitungRekap($allResults);
        $rekapTotal = $this->hitungRekapTotal();

        $sessions = ScanningSession::with('results')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($s) {
                $r = $this->hitungRekap($s->results ?? collect());
                return [
                    'session_id' => $s->id,
                    'status'     => $s->status,
                    'created_at' => $s->created_at,
                    'rekap'      => $r,
                ];
            });

        return response()->json([
            'rekap_semua_session' => $rekap,
            'rekap_total'         => $rekapTotal,
            'per_session'         => $sessions,
        ]);
    }

    // ─────────────────────────────────────────
    // HELPER PRIVATE
    // ─────────────────────────────────────────

    private function hitungRekap($results)
    {
        $totalBuah    = $results->sum('total_buah');
        $countRipe    = $results->sum('count_ripe');
        $countUnripe  = $results->sum('count_unripe');
        $countTurning = $results->sum('count_turning');
        $countBroken  = $results->sum('count_broken');

        if ($totalBuah === 0) {
            foreach ($results as $r) {
                $kat = $r->kategori ?? '';
                if (in_array($kat, ['ripe', 'matang']))                  $countRipe++;
                elseif (in_array($kat, ['unripe', 'mentah']))            $countUnripe++;
                elseif (in_array($kat, ['turning', 'setengah_matang'])) $countTurning++;
                elseif (in_array($kat, ['broken', 'rusak']))             $countBroken++;
            }
            $totalBuah = $countRipe + $countUnripe + $countTurning + $countBroken;
        }

        return [
            'total_buah'    => $totalBuah,
            'count_ripe'    => $countRipe,
            'count_unripe'  => $countUnripe,
            'count_turning' => $countTurning,
            'count_broken'  => $countBroken,
            'total_tanaman' => $results->count(),
        ];
    }

    private function hitungRekapTotal()
    {
        $allResults = ScanningResult::all();
        $rekap      = $this->hitungRekap($allResults);
        $rekap['total_session'] = ScanningSession::count();
        $rekap['session_done']  = ScanningSession::where('status', 'done')->count();
        return $rekap;
    }
}