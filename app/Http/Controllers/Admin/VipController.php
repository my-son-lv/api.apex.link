<?php

namespace App\Http\Controllers\Admin;

use App\Models\Companys;
use App\Models\Vip;
use App\Models\VipAction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class VipController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Vip $vip,Request $request)
    {
        return  $vip->orderBy('id','desc')->paginate($request->pageSize ?? $request->pageSize);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Vip $vip)
    {
        //
        unset($request->user);
        unset($request->token);
        return $vip->create($request->all());
    }



    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Vip $vip)
    {
        //
        return $vip;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,Vip $vip)
    {
        if($request->isMethod('PATCH') && $request->status == 2){
            //查找是否有会员
            $flg = VipAction::where('vip_id',$vip->id)->whereIn('status',[1,2])->count();
            if($flg){
                return $this->fail(1000007);
            }
        }
        //
        unset($request->token);
        unset($request->user);
        $vip->update($request->all());
        return $vip;
    }


}
