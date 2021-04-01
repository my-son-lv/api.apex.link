<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workexperience extends Model
{
    //
    protected $table = 'workexperiences';

    protected $fillable = ['mid','start_time','end_time','company_name','position','work_desc','now','show'];
}
