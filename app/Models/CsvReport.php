<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CsvReport extends Model
{
    protected $table = 'csv_reports';

    protected $fillable = [
        'report_id', 'original_filename',
        'total_vulns', 'critical', 'high', 'medium', 'low', 'info_count',
        'unique_hosts', 'metrics',
    ];

    protected $casts = [
        'metrics'     => 'array',
        'total_vulns' => 'integer',
        'critical'    => 'integer',
        'high'        => 'integer',
        'medium'      => 'integer',
        'low'         => 'integer',
        'info_count'  => 'integer',
        'unique_hosts'=> 'integer',
    ];
}
