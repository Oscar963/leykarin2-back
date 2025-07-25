<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportHistories extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'imported_by',
        'model',
        'status',
        'total_rows',
        'success_count',
        'error_count',
        'error_log',
        'started_at',
        'finished_at',
    ];
}
