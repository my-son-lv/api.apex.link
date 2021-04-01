<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobCollect extends Model
{
    //
    protected $table = 'job_collects';

    protected $fillable = ['mid','jid'];
}
