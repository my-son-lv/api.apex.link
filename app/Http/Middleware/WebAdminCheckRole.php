<?php

namespace App\Http\Middleware;

use App\Models\Menu;
use App\Models\RoleMenu;
use App\Models\UserRole;
use Closure;
use Illuminate\Support\Facades\DB;

class WebAdminCheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $route = $request->route()->getName();
        $menu = Menu::where('route', $route)->get();
        $arr = explode('.', $route);
        $count = count($arr);
        $user = session('admin_user');
        if($user->account != config('admin.SUPER_MANGAGE_ACCOUNT')){
            if ($arr[$count - 1] != 'white' ) {
                $userRole = UserRole::where('user_id', $user['id'])->get(['role_id']);
                if ($userRole->isEmpty()){
                    if (request()->ajax()) {
                        return $this->fail(401);
                    } else {
                        return back()->with('error', '您没有权限');
                    }
                }else{
                    $count = RoleMenu::where('menu_id', $menu[0]->id)->whereIn('role_id', $userRole->toArray())->count();
                    if (!$count) {
                        if (request()->ajax()) {
                            return $this->fail(401);
                        } else {
                            return back()->with('error', '您没有权限');
                        }
                    }
                }
            }
        }
        return $next($request);
    }

    private function fail($code, $data = [])
    {
        return response()->json([
            'code'    => $code,
            'msg' => config('errorcode.code')[(int) $code],
            'data'    => $data,
        ])->setEncodingOptions(JSON_UNESCAPED_UNICODE);
    }
}
