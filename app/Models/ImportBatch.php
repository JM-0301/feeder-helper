<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportBatch extends Model
{
    protected $fillable = [
        'module','filename','stored_path','status',
        'total_rows','valid_rows','invalid_rows','created_by',
    ];

    public function rows()
    {
        return $this->hasMany(ImportRow::class, 'batch_id');
    }
}
