<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeederSyncRun extends Model
{
    protected $fillable = [
        'type','success','message','meta_json','started_at','finished_at'
    ];

    protected $casts = [
        'success' => 'boolean',
        'meta_json' => 'array',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
