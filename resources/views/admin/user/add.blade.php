@extends('admin.layout.main')
@section('menu-check','用户管理')
@section('title', '用户添加')

@section('css')
    <!--css调用-->
    <link href="/static/admin/plugins/select2/dist/css/select2.min.css" rel="stylesheet"></link>

@endsection

@section('content')



    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title"></h4>
                </div>

                <div class="card-body">
                    <form action="{{route('admin.user.add')}}" class="form" method="post" id="dataForm">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">

                        {{--<div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">账号：</label>
                            <div class="col-8">
                                <input type="text" name="account" id="account" placeholder="用户账号" maxlength="20" class="form-control">
                            </div>
                        </div>--}}
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">姓名：</label>
                            <div class="col-8">
                                <input type="text" name="name" id="name" placeholder="用户姓名" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">手机：</label>
                            <div class="col-8">
                                <input type="text" name="phone" id="phone" placeholder="用户手机" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">角色：</label>
                            <div class="col-8">
                                <select class="form-control custom-select" id="test" name="roles[]" multiple="multiple">
                                    @foreach($role as $k => $v)
                                        <option value="{{$v->id}}">{{$v->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">邮箱：</label>
                            <div class="col-8">
                                <input type="email" name="email" id="email" placeholder="用户邮箱" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">密码：</label>
                            <div class="col-8">
                                <input type="text" name="password" id="password" maxlength="20" placeholder="用户密码 不输入默认123456" class="form-control"
                                       value="">
                            </div>
                        </div>

                        {{--<div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">头像：</label>
                            <div class="col-4">
                                <p style="padding-left: 2px;margin-bottom: 0;">建议(长200 * 宽200)</p>
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area"></div>
                                    <input type="hidden" value="" id="img_id" name="img_id">
                                </div>
                            </div>

                        </div>--}}


                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">状态：</label>
                            <div class="col-8 demo-radio-button" style="line-height: 38px;">
                                <input name="status" type="radio" id="radio_1" value="0" checked=""
                                       class="radio-col-light-blue">
                                <label for="radio_1" style="min-width: 80px;">启用</label>
                                <input name="status" type="radio" id="radio_2" value="1" class="radio-col-light-blue">
                                <label for="radio_2">禁用</label>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="offset-sm-2 col-md-8">
                                            {{--<button type="button" class="btn btn-inverse m-l-10"><i
                                                        class="ti-back-right"></i> 返回
                                            </button>--}}
                                            <button type="button" id="submitButton" class="btn btn-info m-r-10"><i
                                                        class="fa fa-check"></i> 提交
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <script src="/static/admin/plugins/select2/dist/js/select2.full.min.js" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            $("#test").select2({
                language: "zh-CN",
                placeholder:'请选择用户角色',
            });

            $("#submitButton").click(function () {
                if ($.trim($("#name").val()).length == 0) {
                    layer.msg('请输入用户姓名', {time: 1000})
                    return false;
                }
                if ($.trim($("#phone").val()).length == 0) {
                    layer.msg('请输入用户手机', {time: 1000})
                    return false;
                }
                if(!(/^1(3|4|5|6|7|8|9)\d{9}$/.test($("#phone").val()))){
                    layer.msg('手机号格式错误', {time: 1000})
                    return false;
                }
                var reslist=$("#test").select2("data");
                if(reslist.length==0){
                    layer.msg('请选择用户角色', {time: 1000})
                    return false;
                }
                if ($.trim($("#email").val()).length == 0) {
                    layer.msg('请输入用户邮箱', {time: 1000})
                    return false;
                }
                if($.trim($("#password").val()).length > 0){
                    if($.trim($("#password").val()).length < 5 || $.trim($("#account").val()).length > 20){
                        layer.msg('用户秘密长度5-20位之间', {time: 1000})
                        return false;
                    }
                }
                $("#dataForm").submit();
            });
        });
    </script>
@endsection


