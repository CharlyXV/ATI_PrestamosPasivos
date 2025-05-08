<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemError extends Model
{
    protected $fillable = [
        'error_code',
        'message',
        'exception',
        'file',
        'line',
        'trace',
        'request_data',
        'user_id',
        'url',
        'ip',
        'user_agent',
    ];
}
