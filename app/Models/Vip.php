<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vip extends Model
{
    //
    protected $table = 'vips';

    protected $fillable = [
        'name',
        'money',
        'month',
        'job_num',
        'top',
        'down',
        'status',
        'visa_coupon',
        'jiping',
        'yaoqing',
        'service',
        'tuisong',
        'show'
    ];
}
