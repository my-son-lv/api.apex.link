<?php

namespace App\Http\Controllers\Index;

use App\Models\Files;
use App\Models\SignContract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    //合同列表
    public function list(Request $request,SignContract $contract){
        $list = $contract->with(['user' => function($query){
            $query->select(['id','name']);
        }])->where('cid',$request->company->id);
        $request->status && $list = $list->where('status',$request->status);
        $list = $list->orderBy('id','desc')
            ->paginate($request->pageSize ?? $request->pageSize);
        return $this->success($list);
    }

    //预览
    public function signView(Request $request){
        $id = $request->get('id',0);
        if(!$id){
            return $this->fail(2000001);
        }
        $model = SignContract::where('id',$id)->where('cid',$request->company->id)->first();
        if(!$model){
            return $this->fail(2000201);
        }
        if($model->status == 3){
            $file = Files::find($model->pdf_file_id);
            return $this->success(['url' => $file->path]);
        }elseif($model->status == 2){
            $token = $this->eSignAutoLogin();
            $url = $this->eSignGetPage($token,$model->flow_id,$model->account_id);
            return $this->success(['url' => $url]);
        }elseif(in_array($model->status,[2, 4, 5])){
            return $this->success(['url' => config('app.url').'/contractTmp?id='.AesEncrypt($model->id)]);
        }
    }
}
