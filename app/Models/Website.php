<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Website extends Model
{
    //
    use SoftDeletes;

    protected $fillable = [
        'name',
        'contact_person',
        'contact_information',
        'ip',
    ];

    protected $hidden = ['deleted_at','updated_at'];
}
