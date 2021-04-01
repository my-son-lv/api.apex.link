<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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
            'phone'     =>  'required',
            'email'     =>  'required|email',
            'status'    =>  'required',
        ];
    }
    public function messages()
    {
        return [
            'phone.required'     => '手机号不能为空',
            'status.required'    => '请选择用户状态',
            'img_id.required'    => '用户头像不能为空',
            'email.required'     => '用户邮箱不能为空',
            'email.email'        => '用户邮箱格式错误',
        ];
    }
}
