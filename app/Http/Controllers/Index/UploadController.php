<?php

namespace App\Http\Controllers\Index;

use App\Models\Files;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    //
    public function upload(Request $request){
        $file = $request->file('file');
        $type = $request->get('type',0); //0图片 1视频

        if(!$file){
            return $this->fail(1000100);
        }
        $fileSize = $file->getSize();
        $fileExt = strtolower($file->getClientOriginalExtension());

        switch ($type){
            case 0:
                //校验图片格式 图片大小
                $configImgMaxSize = config('filesystems.UPLOAD_IMAGE_MAX_SIZE');
//                $configImgExt = config('filesystems.UPLOAD_IMAGE_EXT');
                break;
            case 1:
                //校验视频格式 视频大小
                $configImgMaxSize = config('filesystems.UPLOAD_VIDEOS_MAX_SIZE');
//                $configImgExt = config('filesystems.UPLOAD_VIDEOS_EXT');
                break;
            case 2:
                //校验视频格式 视频大小
                $configImgMaxSize = config('filesystems.UPLOAD_DOC_MAX_SIZE');
//                $configImgExt = config('filesystems.UPLOAD_DOC_EXT');
        }

        if(round($fileSize/1024/1024,2) > $configImgMaxSize){
            return response()->json([
                'code' => 1000100 ,
                'msg' => '文件最大不超过'.$configImgMaxSize.'M'
            ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }

//        if(!in_array($fileExt,explode(',',$configImgExt))){
//            return response()->json([
//                'code' => 1000100 ,
//                'msg' => '文件不在允许的'.$configImgExt.'扩展中'
//            ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
//        }
        $clientName = $file->getClientOriginalName();
        $fileTmp = $file->getRealPath();
        $fileName = date("YmdHis").rand(10000000,99999999).'.'.$fileExt;
        $fileMime = $file->getMimeType();
        $upFilePathName = '/'.config('filesystems.disks.oss.bucket').'/'.$fileName;
        $fileUpFlg = Storage::disk('oss')->put($upFilePathName,file_get_contents($fileTmp));
        $filePath = Storage::disk('oss')->url($upFilePathName);
        if($fileUpFlg){
            #插入数据库
            $fileModel = Files::addFiles($fileName,round($fileSize/1024,2),$fileExt,$filePath,$fileMime,$clientName);
            return $this->success($fileModel);
        }else{
            return $this->fail(1000101);
        }
    }
}
