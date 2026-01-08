<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeederCacheProdi extends Model
{
    protected $table = 'feeder_cache_prodi';

    protected $fillable = [
        'id_prodi_feeder', 'kode_prodi', 'nama_prodi', 'jenjang', 'status',
        'data_json', 'synced_at'
    ];

    protected $casts = [
        'data_json' => 'array',
        'synced_at' => 'datetime',
    ];
}
