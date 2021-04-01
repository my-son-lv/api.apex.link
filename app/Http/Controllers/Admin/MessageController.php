<?php

namespace App\Http\Controllers\Admin;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class MessageController extends Controller
{
    //

    public function list(Message $messages,Request $request){
        $user = User::where('token',$request->token)->first();
        $messages = $messages->where('user_id',$user->id)->orderBy('id','desc')->get();
        return $this->success($messages);
    }


    public function store(Request $request){
        $user = User::where('token',$request->token)->first();
        $msg  = $request->get('msg','');
        if(!$msg){
            return $this->fail(100001);
        }
        if(Message::where('user_id',$user->id)->count()>=15){
            return $this->fail(2000208);
        }
        $model = new Message();
        $model->user_id = $user->id;
        $model->msg     = $msg;
        if($model->save()){
            return $this->success($model);
        }else{
            return $this->fail();
        }
    }


    public function update(Request $request){
        $user = User::where('token',$request->token)->first();
        $msg  = $request->get('msg','');
        $id   = $request->get('id','');
        if(!$msg || !$id){
            return $this->fail(100001);
        }
        $model = Message::where('id',$id)->where('user_id',$user->id)->first();
        if(!$model){
            return $this->fail(2000201);
        }
        $model->user_id = $user->id;
        $model->msg     = $msg;
        if($model->save()){
            return $this->success($model);
        }else{
            return $this->fail();
        }
    }

    public function show(Request $request){
        $user = User::where('token',$request->token)->first();
        $id   = $request->get('id','');
        if(!$id){
            return $this->fail(100001);
        }
        $model = Message::where('id',$id)->where('user_id',$user->id)->first();
        if(!$model){
            return $this->fail(2000201);
        }
        return $this->success($model);
    }

    public function destroy(Request $request){
        $user = User::where('token',$request->token)->first();
        $id   = $request->get('id','');
        if(!$id){
            return $this->fail(100001);
        }
        $flg = Message::where('id',$id)->where('user_id',$user->id)->delete();
        if($flg){
            return $this->success();
        }else{
            return $this->fail();
        }
    }
}
