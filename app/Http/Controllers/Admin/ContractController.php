<?php

namespace App\Http\Controllers\Admin;

use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractController extends Controller
{
    //获取合同模板列表
    public function list(Request $request,Contract $contract){
        $list = $contract->orderBy('id','desc')->get(['id','name','created_at']);
        return $this->success($list);
    }
}
