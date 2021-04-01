<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Down extends Model
{
    protected $table = 'downs';

    protected $fillable = [
        'mid',
        'cid',
        'vip_id',
    ];
}
