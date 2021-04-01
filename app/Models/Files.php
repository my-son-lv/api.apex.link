<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Files extends Model
{
    protected $table = 'files';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 通用文件插入
     * @param $name
     * @param $size
     * @param $ext
     * @param $path
     * @param $mime
     * @return Files|bool
     */
    public static function addFiles($name,$size,$ext,$path,$mime,$fileName){
        $model = new Files();
        $model->name = $name;
        $model->size = $size;
        $model->ext  = $ext;
        $model->path = $path;
        $model->mime = $mime;
        $model->file_name = $fileName;
        if($model->save()){
            return $model;
        }else{
            return false;
        }
    }


}
