<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    //
    protected $table = 'officials';

    protected $fillable = [
        "openid",
        "nickname",
        "sex",
        "city",
        "province",
        "country",
        "headimgurl",
        "unionid",
        "subscribe_scene",
        "status",
        "time"
    ];
}
