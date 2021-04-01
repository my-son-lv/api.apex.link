<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name'      =>  'required',
            'menu_list' =>  'required',
        ];
    }
    public function messages()
    {
        return [
            'name.required'      => '角色名称不能为空',
            'name.unique'        => '角色名称已存在',
            'menu_list.required' => '操作菜单不能为空',
        ];
    }
}
