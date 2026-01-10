<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportRow extends Model
{
    protected $fillable = [
        'batch_id','row_number','status','data_json','error_json','validated_at'
    ];

    protected $casts = [
        'data_json' => 'array',
        'error_json' => 'array',
        'validated_at' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'batch_id');
    }
}
