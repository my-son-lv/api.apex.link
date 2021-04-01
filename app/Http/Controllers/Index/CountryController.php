<?php

namespace App\Http\Controllers\Index;

use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CountryController extends Controller
{
    //获取国籍列表
    public function getCountryList(){
        $list = Country::orderBy('code','asc')->get();
        return $this->success($list);
    }


}
