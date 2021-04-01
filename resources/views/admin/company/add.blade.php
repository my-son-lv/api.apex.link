@extends('admin.layout.main')
@section('menu-check','企业添加')
@section('title', '企业添加')

@section('css')
    <!--css调用-->
    <style>
        .del_file_btn{
            position: absolute;
            right: 18%;
            top: 5%;
            background: rgb(186 187 191 / 80%);
            height: 30px !important;
            width: 30px;
            line-height: 30px;
            text-align: center;
            border-radius: 50%;
            color: white;
            font-size: 23px;
            font-weight: 900;
            display: none;
            z-index: 100;
        }
    </style>
@endsection

@section('content')



    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title"></h4>
                </div>

                <div class="card-body">
                    <form action="{{route('admin.company.add')}}" class="form" method="post" id="dataForm">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">
                        <label>必填项</label>
                        <hr>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>机构名称 ：
                            </label>
                            <div class="col-3">
                                <input type="text" name="company_name" placeholder="机构名称" id="company_name" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>手机号码 ：
                            </label>
                            <div class="col-3">
                                <input type="text" name="phone" placeholder="登录手机号码" id="phone" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>机构类型 ：
                            </label>
                            <div class="col-8 demo-radio-button" style="line-height: 38px;">
                                <input name="type" type="radio" id="radio_1" value="1" checked=""
                                       class="radio-col-light-blue">
                                <label for="radio_1" style="min-width: 80px;">培训机构</label>
                                <input name="type" type="radio" id="radio_2" value="2" class="radio-col-light-blue">
                                <label for="radio_2" style="min-width: 80px;">公立学校</label>
                                <input name="type" type="radio" id="radio_3" value="3" class="radio-col-light-blue">
                                <label for="radio_3" style="min-width: 80px;">私立学校</label>
                                <input name="type" type="radio" id="radio_4" value="4" class="radio-col-light-blue">
                                <label for="radio_4" style="min-width: 80px;">中介机构</label>
                                <input name="type" type="radio" id="radio_5" value="5" class="radio-col-light-blue">
                                <label for="radio_5" style="min-width: 80px;">幼儿园</label>
                                <input name="type" type="radio" id="radio_6" value="6" class="radio-col-light-blue">
                                <label for="radio_6" style="min-width: 80px;">其他</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>办公地址 ：
                            </label>
                            <div class=" col-lg-1">
                                <select class="form-control custom-select" id="pro1">
                                    <option value="0" >省</option>
                                    @foreach($province as $k => $v){
                                    <option value="{{$v->id}}" >{{$v->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class=" col-lg-1">
                                <select class="form-control custom-select" id="working_city">
                                    <option value="0" >市</option>
                                </select>
                            </div>
                            <div class=" col-lg-1">
                                <select class="form-control custom-select" name="city" id="city">
                                    <option value="0" >区</option>
                                </select>
                            </div>

                            <div class="col-5">
                                <input type="text" name="address" placeholder="详细地址" id="address" class="form-control">
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>营业执照 ：
                            </label>
                            <div class="col-2 demo-radio-button" style="line-height: 38px;">
                                <input name="business_flg" type="radio" id="radio_7" value="1"
                                       class="radio-col-light-blue">
                                <label for="radio_7" style="min-width: 80px;">具备</label>
                                <input name="business_flg" type="radio" id="radio_8" value="0" checked="" class="radio-col-light-blue">
                                <label for="radio_8" style="min-width: 80px;">不具备</label>
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>办学许可资质 ：
                            </label>
                            <div class="col-2 demo-radio-button" style="line-height: 38px;">
                                <input name="talent" type="radio" id="radio_9" value="1"
                                       class="radio-col-light-blue">
                                <label for="radio_9" style="min-width: 80px;">具备</label>
                                <input name="talent" type="radio" id="radio_10" value="0" class="radio-col-light-blue" checked="">
                                <label for="radio_10" style="min-width: 80px;">不具备</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>学生年龄 ：
                            </label>
                            <div class="col-8 demo-checkbox" style="line-height: 38px;">
                                <input  value="1" type="checkbox" id="basic_checkbox_1" class="chk-col-light-blue student_age"  />
                                <label for="basic_checkbox_1">3岁以下</label>

                                <input  value="2" type="checkbox" id="basic_checkbox_2" class="chk-col-light-blue student_age"  />
                                <label for="basic_checkbox_2">3-6岁</label>

                                <input  value="3" type="checkbox" id="basic_checkbox_3" class="chk-col-light-blue student_age"  />
                                <label for="basic_checkbox_3">7-12岁</label>

                                <input  value="4" type="checkbox" id="basic_checkbox_4" class="chk-col-light-blue student_age"  />
                                <label for="basic_checkbox_4">13-18岁</label>

                                <input  value="5" type="checkbox" id="basic_checkbox_5" class="chk-col-light-blue student_age"  />
                                <label for="basic_checkbox_5">18岁以上</label>

                                <input type="hidden" name="student_age" id="student_age">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>外籍员工数 ：
                            </label>
                            <div class="col-3">
                                <input type="number" name="abroad_staff" placeholder="外籍员工数(名)例：100" min="0" id="abroad_staff" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>年度外教需求量 ：
                            </label>
                            <div class=" col-lg-3">
                                <select class="form-control custom-select" id="needs_num" name="needs_num">
                                    <option value="1" >1-10人</option>
                                    <option value="2" >11-20人</option>
                                    <option value="3" >21-50人</option>
                                    <option value="4" >51人以上</option>
                                </select>
                            </div>

                        </div>


                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>月均薪资范围(税后) ：
                            </label>
                            <div class=" col-lg-3">
                                <select class="form-control custom-select" id="pay" name="pay">
                                    <option value="1" >15000元以下</option>
                                    <option value="2" >15000-20000元</option>
                                    <option value="3" >20000元以上</option>
                                </select>
                            </div>
                        </div>

                        <hr>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>紧急联系人 ：
                            </label>
                            <div class="col-3">
                                <input type="text" name="contact" placeholder="紧急联系人" min="0" id="contact" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>联系电话 ：
                            </label>
                            <div class="col-3">
                                <input type="text" name="contact_phone" placeholder="联系电话" min="0" id="contact_phone" class="form-control">
                            </div>


                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                工作邮箱 ：
                            </label>
                            <div class="col-3">
                                <input type="text" name="work_email" placeholder="工作邮箱" min="0" id="work_email" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>所属顾问 ：
                            </label>
                            <div class=" col-lg-3">
                                <select class="form-control custom-select" id="user" name="user">
                                    @foreach($user as $k => $v)
                                        <option value="{{$v->id}}" @if( $v->id == session('admin_user')['id'])  selected  @endif >{{$v->name}}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <label>非必填项</label>
                        <hr>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                公司名称 ：
                            </label>
                            <div class="col-8">
                                <input type="text" name="business_name" placeholder="公司名称" id="business_name" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                机构LOGO ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area"></div>
                                    <input type="hidden" value="" id="logo" name="logo">
                                </div>
                            </div>


                            <label for="example-text-input" class="col-1 col-form-label text-right">
                                营业执照 ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area1"></div>
                                    <input type="hidden" value="" id="business_img" name="business_img">
                                </div>
                            </div>


                            <label for="example-text-input" class="col-1 col-form-label text-right">
                                办学许可资质证书 ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area4"></div>
                                    <input type="hidden" value="" id="talent_img" name="talent_img">
                                </div>
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                校区图片一 ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area2"></div>
                                    <input type="hidden" value="" id="school_img_1" name="school_img_1">
                                </div>
                            </div>


                            <label for="example-text-input" class="col-1 col-form-label text-right">
                                校区图片二 ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area3"></div>
                                    <input type="hidden" value="" id="school_img_2" name="school_img_2">
                                </div>
                            </div>
                        </div>



                        <div class="form-actions">
                            <div class="row">
                                <div class="offset-sm-2 col-md-8">
                                    {{--<button type="button" class="btn btn-inverse m-l-10"><i
                                                class="ti-back-right"></i> 返回
                                    </button>--}}
                                    <button type="button" id="submitForm" class="btn btn-info m-r-10"><i
                                                class="fa fa-check"></i> 提交
                                    </button>
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
    <script src="/static/admin/plugins/img-upload/js/upload.js?1=10" type="text/javascript"></script>
    <script>
        $(document).ready(function () {
            $("#phone").blur(function () {
                var phone = $.trim($(this).val());
                if(phone.length > 1){
                    $.post("{{url('/api/company/isPhoneExist')}}",{'phone':phone},function (res) {
                        if(res.code == 200){
                            if(res.data.flg == true){
                                $("#phone").css('border-color','#ef5350');
                                layer.msg('用户手机号已存在,请修改用户手机号');
                            }else{
                                $("#phone").css('border-color','#ced4da');
                            }
                        }else{
                            layer.msg(res.msg);
                        }
                    })
                }
            });

            var getCity = function (id,code,name='市') {
                $.ajax({
                    url: "{{url('/api/index/getCity')}}",
                    type: 'post',
                    data: {'id':code},
                    success: function (res) {
                        if (res.code == 200) {
                            $(id).empty();
                            $(id).append('<option value="0" >'+name+'</option>');
                            $.each(res.data,function(k,v){
                                $(id).append('<option value="'+v.id+'" >'+v.name+'</option>');
                            })
                        }
                    },
                    errors: function(msg){
                        console.log(msg);
                    }
                })
            }
            $("#pro1").change(function () {
                $("#city").empty();
                $("#city").append('<option value="0" >区</option>');
                $("#working_city").empty();
                $("#working_city").append('<option value="0" >市</option>');
                getCity("#working_city",$(this).val());
            });
            $("#working_city").change(function () {
                getCity("#city",$(this).val(),'区');
            });

            var fileUpload = function (id,preId,type,accept = '') {
                var dragImgUpload = new DragImgUpload(id,accept, {
                    callback: function (files) {
                        //回调函数，可以传递给后台等等
                        var file = files[0];
                        var fromData = new FormData();
                        fromData.append("file", file);
                        if(type == 1){
                            fromData.append("type", 1);
                        }
                        $.ajax({
                            url: "{{url('api/index/upload')}}",
                            type: 'post',
                            processData: false,
                            contentType: false,
                            data: fromData,
                            success: function (res) {
                                if (res.code == 200) {
                                    $(preId).val(res.data.id);
                                    console.log(res.data.id);
                                }
                            },
                            errors: function(msg){
                                console.log(msg);
                            }
                        })
                    }
                })
            }
            $(document).on('click','.del_file_btn',function (event) {
                $(this).hide();
                var par = $(this).prev().remove();
                $(this).parent().prepend('<div id="preview"><img src="/static/admin/plugins/img-upload/img/upload.png" class="img-responsive"  style="width: 100%;height: auto;border-radius: 4px;border: 1px solid #eee;" alt="" title=""></div>');
                $(this).parent().parent().find("input").val('');
                $("#plugeins_uploadFiles").val('');
                // event.stopPropagation();
                cancelBubble();
            });
            function cancelBubble(e) {
                var evt = e ? e : window.event;
                if (evt.stopPropagation) {        //W3C
                    evt.stopPropagation();
                }else {       //IE
                    evt.cancelBubble = true;
                }
            }
            fileUpload("#drop_area","#logo",0);
            fileUpload("#drop_area1","#business_img",0);
            fileUpload("#drop_area2","#school_img_1",0);
            fileUpload("#drop_area3","#school_img_2",0);
            fileUpload("#drop_area4","#talent_img",0);
            $("#submitForm").click(function () {

                if ($.trim($("#company_name").val()).length == 0) {
                    layer.msg('请输入机构名称', {time: 1000})
                    return false;
                }
                if ($.trim($("#phone").val()).length == 0) {
                    layer.msg('请输入登录手机号', {time: 1000})
                    return false;
                }
                if ($.trim($("#city").val()).length == 0) {
                    layer.msg('请选择办公地址', {time: 1000})
                    return false;
                }
                if ($.trim($("#address").val()).length == 0) {
                    layer.msg('请输入详细办公地址', {time: 1000})
                    return false;
                }
                var str = '';
                $.each($("input[type=checkbox]:checked"),function(i){
                    str += $(this).val() + ",";
                });
                str = str.substr(0,str.length - 1);
                $("#student_age").val(str);
                if(str.length == 0){
                    layer.msg('请选择学生年龄', {time: 1000})
                    return false;
                }
                if ($.trim($("#abroad_staff").val()).length == 0 || parseInt($("#abroad_staff").val())<1) {
                    layer.msg('请输入外籍员工数', {time: 1000})
                    return false;
                }
                if ($.trim($("#abroad_staff").val()).length == 0) {
                    layer.msg('请选择年度外教需求量', {time: 1000})
                    return false;
                }
                if ($.trim($("#pay").val()).length == 0) {
                    layer.msg('请选择月均薪资范围', {time: 1000})
                    return false;
                }
                if ($.trim($("#pay").val()).length == 0) {
                    layer.msg('请选择月均薪资范围', {time: 1000})
                    return false;
                }
                if ($.trim($("#contact").val()).length == 0) {
                    layer.msg('请输入紧急联系人', {time: 1000})
                    return false;
                }
                if ($.trim($("#contact_phone").val()).length == 0) {
                    layer.msg('请输入紧急联系人电话', {time: 1000})
                    return false;
                }
               /* if ($.trim($("#work_email").val()).length == 0) {
                    layer.msg('请输入紧急联系人工作邮箱', {time: 1000})
                    return false;
                }*/
                layer.confirm('提交信息后，将正式录入，并在平台展示。</br>请仔细审核填写资料,确认提交？', {
                    btn: ['提交','我在看看'] //按钮
                }, function(){
                    $("#dataForm").submit();
                });
            });
        })
    </script>
@endsection


