<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LOGS extends Model
{
    protected $connection = 'mysql';
    protected $table = 'logs';
    protected $fillable = [
        'ip',
        'log_host',
        'log_time',
        'log_time_date',
        'log_headers' ,
        'log_url' ,
        'log_method',
        'log_request',
        'log_response',
        'log_response_status',
        'log_user_id',
        'log_user_name'
    ];
}
