<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
    //
    protected $table = 'notices';

    protected $fillable = [ 'content', 'to_uid', 'type', 'code', 'read_flg' ];

    public static function addNotice($content,$type,$code,$to_uid = []){
        if(!count($to_uid)){
            $to_uid = User::where('status',0)->get();
        }
        foreach ($to_uid as $k => $v){
            self::create([
                'content'   => $content,
                'type'      => $type,
                'to_uid'    => $v['id'],
                'code'      => $code,
            ]);
        }
    }
}
