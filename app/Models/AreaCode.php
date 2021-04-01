<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AreaCode extends Model
{
    //
    protected $table = 'area_codes';

    protected $fillable = ['titleKey','typeName','parentId','index','value'];
}
