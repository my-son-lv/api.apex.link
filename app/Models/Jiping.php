<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jiping extends Model
{
    //
    protected $table = 'jipings';

    protected $fillable = ['cid','jid','vip_id'];
}
