<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publicity extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','phone', 'memo'
    ];
}
