<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeederStatsSnapshot extends Model
{
    protected $fillable = [
        'snapshot_date',
        'total_mahasiswa',
        'total_dosen',
        'raw_json',
        'synced_at',
    ];

    protected $casts = [
        'snapshot_date' => 'date',
        'raw_json' => 'array',
        'synced_at' => 'datetime',
    ];
}
