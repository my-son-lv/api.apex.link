<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobMate extends Model
{
    //
    protected $table = 'job_mates';

    protected $fillable = ['jid','mid','level'];
    

}
