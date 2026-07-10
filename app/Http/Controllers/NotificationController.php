<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
    /** Simpan email penerima + aktif/nonaktif notifikasi. */
    public function save(Request $r)
    {
        $r->validate(['notify_email' => 'nullable|email']);

        DB::table('app_settings')->updateOrInsert(
            ['name' => 'notify_email'],
            ['value' => $r->input('notify_email')]
        );
        DB::table('app_settings')->updateOrInsert(
            ['name' => 'notify_enabled'],
            ['value' => $r->has('notify_enabled') ? '1' : '0']
        );

        return back()->with('success', 'Pengaturan notifikasi disimpan.');
    }

    /** Kirim email uji ke alamat yang tersimpan. */
    public function test()
    {
        $to = DB::table('app_settings')->where('name', 'notify_email')->value('value');
        if (! $to) {
            return back()->with('error', 'Isi dan simpan email penerima terlebih dahulu.');
        }

        try {
            $body = "Email uji dari Greenhouse Monitor.\n\n"
                  . "Jika pesan ini diterima, konfigurasi notifikasi email sudah berfungsi.\n"
                  . "Waktu: " . now()->timezone('Asia/Jakarta')->format('d/m/Y H:i:s') . " WIB\n\n"
                  . "-- Greenhouse Monitor";

            Mail::raw($body, function ($m) use ($to) {
                $m->to($to)->subject('[UJI] Greenhouse Monitor');
            });

            return back()->with('success', 'Email uji terkirim ke ' . $to . '. Periksa inbox atau folder spam.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal mengirim: ' . $e->getMessage());
        }
    }
}