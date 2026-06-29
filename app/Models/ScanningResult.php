<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScanningResult extends Model
{
    protected $fillable = [
        'session_id',
        'nomor_tanaman',
        'baris',
        'kolom',
        'ripeness_score',
        'kategori',
        'image_path',
        'total_buah',
        'count_ripe',
        'count_unripe',
        'count_turning',
        'count_broken',
    ];

    protected $casts = [
        'total_buah'    => 'integer',
        'count_ripe'    => 'integer',
        'count_unripe'  => 'integer',
        'count_turning' => 'integer',
        'count_broken'  => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(ScanningSession::class, 'session_id');
    }
}