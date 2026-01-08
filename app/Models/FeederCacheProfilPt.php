<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeederCacheProfilPt extends Model
{
    protected $table = 'feeder_cache_profil_pt';

    protected $fillable = ['data_json', 'synced_at'];

    protected $casts = [
        'data_json' => 'array',
        'synced_at' => 'datetime',
    ];
}
