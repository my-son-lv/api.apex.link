<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Advert extends Model
{
    use SoftDeletes;

    protected $table = 'adverts';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected  $hidden = ['deleted_at'];
    protected $fillable = [
        'title',
        'start_time',
        'end_time',
        'type',
        'status',
        'img1',
        'url1',
        'img2',
        'url2',
        'img3',
        'url3',
        'img4',
        'url4',
    ];
}
