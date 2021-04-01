<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tuisong extends Model
{
    protected $table = 'tuisongs';

    protected $fillable = ['cid','jid','vip_id'];
}
