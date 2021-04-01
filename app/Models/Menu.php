<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'admin_menu';

    /**
     * 该模型是否被自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    protected $hidden = ['delete'];

    public function getMenuList(){
        $user = $user = session('admin_user');
        $menu = Menu::where(['status' => 0 , 'delete' => 0]);
        if($user['account'] != config('admin.SUPER_MANGAGE_ACCOUNT')){
            $roleIdArr = UserRole::where('user_id',$user['id'])->get(['role_id']);
            $menuIdArr = RoleMenu::whereIn('role_id',$roleIdArr)->get(['menu_id']);
            $menu = $menu->whereIn('id',$menuIdArr);
        }
        $menu = $menu->orderBy('sort','asc')->orderBy('id','desc')->get();
        return $menu;
    }
}
