<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeederSetting extends Model
{
    protected $fillable = [
        'ws_url',
        'username',
        'password',
        'timeout',
    ];

    protected $casts = [
        'username' => 'encrypted',
        'password' => 'encrypted',
        'timeout'  => 'integer',
    ];
}
