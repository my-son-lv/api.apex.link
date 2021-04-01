@extends('admin.layout.main')
@section('menu-check','外教添加')
@section('title', '外教添加')

@section('css')
    <!--css调用-->
    <link href="/static/admin/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.css" rel="stylesheet" />
    <!--ztree start-->
    <link rel="stylesheet" href="/static/admin/plugins/ztree/bootstrapStyle/bootstrapStyle.css" type="text/css">
    <link href="/static/admin/plugins/select2/dist/css/select2.min.css" rel="stylesheet"></link>
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
        .select2-selection__rendered{
            display: none;
        }
        .ztree{
            border: solid 1px #eee;
            z-index: 9999;
            position: absolute;
            background: white;
            width: inherit;
            color: #67757c;
            min-height: 38px;
            padding: .375rem .75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #495057;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da;
            border-radius: .25rem;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            background: white;
            display: none;
            z-index: 9999;
            overflow: scroll;
            height: 300px;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title">外教快捷录入系统</h4>
                </div>

                <div class="card-body">
                    <form action="{{url('admin/member/add')}}" class="form" method="post"  id="dataForm">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <label>基本信息</label>
                        <hr>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Email ：
                            </label>
                            <div class="col-6">
                                <input type="email" name="email" id="email" placeholder="Email" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>User Name：
                            </label>
                            <div class="col-6">
                                <input type="text" name="nick_name" id="nick_name" placeholder="User Name" class="form-control">
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>First Name ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="name" id="name" placeholder="First Name" class="form-control">
                            </div>
                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                Last Name ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="last_name" id="last_name" placeholder="LAST NAME" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Gender ：
                            </label>
                            <div class="col-6 demo-radio-button" style="line-height: 38px;">
                                <input name="sex" type="radio" id="radio_1" value="0"
                                       class="radio-col-light-blue">
                                <label for="radio_1" style="min-width: 80px;">MALE</label>
                                <input name="sex" type="radio" id="radio_2" value="1" class="radio-col-light-blue">
                                <label for="radio_2" style="min-width: 80px;">FEMALE</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Date of birth ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="brithday" id="brithday" placeholder="Date of birth" class="form-control" readonly>
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>Nationality ：
                            </label>
                            <div class=" col-lg-2">
                                <select class="form-control custom-select" id="nationality" name="nationality" class="form-control">
                                    @foreach($countrys as $k => $v)
                                        <option value="{{$v->id}}">{{$v->code}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Are you in China ：
                            </label>
                            <div class="col-6 demo-radio-button" style="line-height: 38px;">
                                <input name="in_domestic" type="radio" id="radio_3" value="1" class="radio-col-light-blue in_domestic">
                                <label for="radio_3" style="min-width: 80px;">Yes</label>
                                <input name="in_domestic" type="radio"  id="radio_4" value="0" class="radio-col-light-blue in_domestic" checked>
                                <label for="radio_4" style="min-width: 80px;">No</label>
                            </div>
                        </div>
                        <!-- in_china == 1-->
                        <div class="form-group row in_domestic_1">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                City ：
                            </label>
                            <div class=" col-lg-3">
                                <select class="form-control custom-select" id="pro1">
                                    <option value="0">Province</option>
                                    @foreach($province as $k => $v){
                                    <option value="{{$v->id}}" >{{$v->pinyin}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class=" col-lg-3">
                                <select class="form-control custom-select" name="china_address" id="china_address">
                                    <option value="0" >City</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row in_domestic_1">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Visa Type ：
                            </label>
                            <div class=" col-lg-2">
                                <select class="form-control custom-select" id="visa_type" name="visa_type">
                                    <option value="">Visa Type</option>
                                    <option value="1">Z</option>
                                    <option value="2">M</option>
                                    <option value="3">F</option>
                                    <option value="4">X</option>
                                    <option value="5">other</option>
                                </select>
                            </div>


                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                Date of Expiry ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="visa_exp_date" id="visa_exp_date" placeholder="Date of Expiry" class="form-control" readonly>
                            </div>
                        </div>
                        <!-- in_china == 1-->

                        <!-- in_china == 0-->
                        <div class="form-group row in_domestic_0">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Address ：
                            </label>
                            <div class=" col-lg-6">
                                <select class="form-control custom-select" id="country" name="country" class="form-control">
                                    @foreach($countrys as $k => $v)
                                        <option value="{{$v->id}}">{{$v->code}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- in_china == 0-->

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Phone Number ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="phone" id="phone" placeholder="Phone Number" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                Wechat：
                            </label>
                            <div class="col-2">
                                <input type="text" name="wechat" id="wechat" placeholder="Wechat" class="form-control">
                            </div>
                        </div>

                    <label>学历信息</label>
                    <hr>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Name of University ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="school" id="school" placeholder="Name of University" class="form-control">
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                Major ：
                            </label>
                            <div class="col-2">
                                <input type="text" name="major" id="major" placeholder="Major" class="form-control">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Highest Academic Degree ：
                            </label>
                            <div class=" col-lg-6">
                                <select class="form-control custom-select" name="university" id="university">
                                    <option value="1" >High School Diploma</option>
                                    <option value="2" >Associate's Degree</option>
                                    <option value="3" >Bachelor's Degree</option>
                                    <option value="4" >Master's Degree</option>
                                    <option value="5" >MBA</option>
                                    <option value="6" >PHD</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                TEFL ：
                            </label>
                            <div class="col-2 demo-radio-button" style="line-height: 38px;">
                                <input name="edu_cert_flg" type="radio" id="radio_11" value="1"
                                       class="radio-col-light-blue">
                                <label for="radio_11" style="min-width: 80px;">Yes</label>
                                <input name="edu_cert_flg" type="radio" id="radio_12" value="0" class="radio-col-light-blue" checked>
                                <label for="radio_12" style="min-width: 80px;">No</label>
                            </div>


                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                TESOL ：
                            </label>
                            <div class="col-2 demo-radio-button" style="line-height: 38px;">
                                <input name="edu_auth_flg" type="radio" id="radio_13" value="1"
                                       class="radio-col-light-blue">
                                <label for="radio_13" style="min-width: 80px;">Yes</label>
                                <input name="edu_auth_flg" type="radio" id="radio_14" value="0" class="radio-col-light-blue" checked>
                                <label for="radio_14" style="min-width: 80px;">No</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                TEFL ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area3"></div>
                                    <input type="hidden" value="" id="edu_cert_imgs" name="edu_cert_imgs">
                                </div>
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                TESOL ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area4"></div>
                                    <input type="hidden" value="" id="edu_auth_imgs" name="edu_auth_imgs">
                                </div>
                            </div>
                        </div>

                        <label>工作信息</label>
                        <hr>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Working years ：
                            </label>
                            <div class=" col-lg-2">
                                <select class="form-control custom-select" name="working_seniority" id="working_seniority">
                                    <option value="1" >No any work experience yet</option>
                                    <option value="2" >Less than 1 year</option>
                                    <option value="3" >1 - 3 years</option>
                                    <option value="4" >3 - 5 years</option>
                                    <option value="5" >5 - 10 years</option>
                                    <option value="6" >More than 10 year</option>
                                </select>
                            </div>

                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                Still in role ：
                            </label>
                            <div class="col-2 demo-radio-button" style="line-height: 38px;">
                                <input name="work_flg" type="radio" id="radio_7" value="1"
                                       class="radio-col-light-blue">
                                <label for="radio_7" style="min-width: 80px;">YES</label>
                                <input name="work_flg" type="radio" id="radio_8" value="0" class="radio-col-light-blue"  checked="">
                                <label for="radio_8" style="min-width: 80px;">NO</label>
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Salary Expectation：
                            </label>
                            <div class=" col-lg-6">
                                <select class="form-control custom-select" name="pay_type" id="pay_type">
                                    <option value="1" >10K-13K</option>
                                    <option value="2" >13K-16K</option>
                                    <option value="3" >16K-20K</option>
                                    <option value="4" >20K-25K</option>
                                    <option value="5" >>25K   </option>
                                </select>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Preferred cities to work-if any ：
                            </label>
                            <div class="col-6 city_xuanze">
                                <input type="text" value="" id="working_city" name="working_city" data-role="tagsinput" placeholder="City" class="form-control" >
                                <div id="menuContent" class="btn-white dropdown-toggle col-sm-11"
                                     style="display:none; position: absolute;z-index:999;border-radius: 3px;padding: 0;">
                                    <ul id="treeDemo" class="ztree col-sm-11"></ul>
                                </div>
                            </div>
                        </div>



                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Upload your resume ：
                            </label>
                            <div class="col-6">
                                <input type="file" class="form-control" id="notes_upload">
                                <input type="hidden" value="" id="notes" name="notes">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                Self introduction (text) ：
                            </label>
                            <div class="col-6">
                                <textarea id="desc" name="desc" rows="6" placeholder="Please talk about yourself; your interests, hobbies and your previous work experience. Tell us why you want to come to China." class="form-control"></textarea>
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="example-text-input" class="col-4 col-form-label text-right">
                                <label style="color:red;">*</label>Upload a photo ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area"></div>
                                    <input type="hidden" value="" id="photos" name="photos">
                                </div>
                            </div>


                            <label for="example-text-input" class="col-2 col-form-label text-right">
                                <label style="color:red;">*</label>Upload a video ：
                            </label>
                            <div class="col-2">
                                <div class="col-12" style="padding-left: 0px;">
                                    <div id="drop_area1"></div>
                                    <input type="hidden" value="" id="videos" name="videos">
                                </div>
                            </div>
                        </div>


                        <div class="form-group row">
                            <div class="offset-sm-4">
                                <button id="submitForm" type="button" class="btn btn-info m-r-10">
                                    <i class="fa fa-check"></i> 提交
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
    <!-- 日期插件 JavaScript -->
    <script src="/static/admin/plugins/laydate/laydate.js"></script>
    <script src="/static/admin/plugins/img-upload/js/upload.js?1=9" type="text/javascript"></script>
    <script src="/static/admin/plugins/bootstrap-tagsinput/dist/bootstrap-tagsinput.min.js"></script>
    <!--ztree start-->
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.core.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.excheck.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.exedit.js"></script>

    <script type="text/javascript" src="/static/admin/plugins/select2/dist/js/select2.full.min.js" ></script>
    <script>

        //国际版
        laydate.render({
            elem: '#brithday'
            ,lang: 'en'
        });

        //国际版
        laydate.render({
            elem: '#work_start_time'
            ,lang: 'en'
        });

        //国际版
        laydate.render({
            elem: '#work_end_time'
            ,lang: 'en'
        });

        //国际版
        laydate.render({
            elem: '#visa_exp_date '
            ,lang: 'en'
        });

        $("#nationality").select2({
            language: "zh-CN",
            placeholder:'nationality',
        });
        $("#country").select2({
            language: "zh-CN",
            placeholder:'Address',
        });
        $("#pro1").select2({
            language: "zh-CN",
            placeholder:'Province',
        });
        $("#china_address").select2({
            language: "zh-CN",
            placeholder:'city',
        });



        $(".in_domestic_1").hide();
        $(".in_domestic_0").show();
        $(".in_domestic").click(function () {
            var val = $(this).val();
            if(val == 1){
                $(".in_domestic_0").hide();
                $(".in_domestic_1").show();
            }else{
                $(".in_domestic_1").hide();
                $(".in_domestic_0").show();
            }
        });

        var setting = { data: {simpleData: {enable: true}},callback: {onClick: onClick}};
        var zNodes = [];
        $.post({url:"{{route('admin.public.getCityZtree')}}",type:"POST",async:false,dataType:'json',data:{'_token' : "{{csrf_token()}}"},success:function(res){zNodes = res;}});
        $.fn.zTree.init($("#treeDemo"), setting, zNodes);
        $("#working_city").tagsinput({
            freeInput:false,
            allowDuplicates:false,
            trimValue:true,
            itemValue:'id',
            itemText:'name',
            interactive:false,
            maxTags: 3,
        });
        $('#working_city').on('beforeItemRemove', function(event) {
            var tag = event.item;
            console.log(tag);

        });
        function onClick(e, treeId, treeNode) {
            if(treeNode.pId){
                $("#working_city").tagsinput('add',{ "id": treeNode.id , "name": treeNode.name});
                $("#menuContent").fadeOut("fast");
            }
        }
        $(document).on('click','.bootstrap-tagsinput',function () {
            if($(".ztree").is(":hidden")){
                $(".ztree").show();
                $("#menuContent").css("0px", "0px").slideDown("fast");
            }else{
                $(".ztree").hide();
                $("#menuContent").fadeOut("fast");
            }
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
                                $(preId).prev().children().children().attr('src',res.data.path)
                            }
                        },
                        errors: function(msg){
                            console.log(msg);
                        }
                    })
                }
            })
        }
        var getCity = function (id,province) {
            $.ajax({
                url: "{{url('/api/index/getCity')}}",
                type: 'post',
                data: {'id':province},
                success: function (res) {
                    if (res.code == 200) {
                        $(id).empty();
                        $(id).append('<option value="0" >City</option>');
                        $.each(res.data,function(k,v){
                            $(id).append('<option value="'+v.id+'" >'+v.pinyin+'</option>');
                        })
                    }else{
                        layer.msg(res.msg);
                    }
                },
                errors: function(msg){
                    console.log(msg);
                }
            })
        }
        $("#pro1").change(function () {
            getCity("#china_address",$(this).val());
        });



        $("#notes_upload").change(function () {
            console.log($(this)[0].files[0]);
            var file = $(this)[0].files[0];
            if($(this).val()){
                var fromData = new FormData();
                fromData.append("file", file);
                fromData.append("type", 2);
                $.ajax({
                    url: "{{url('api/index/upload')}}",
                    type: 'post',
                    processData: false,
                    contentType: false,
                    data: fromData,
                    success: function (res) {
                        if (res.code == 200) {
                            $("#notes").val(res.data.id);
                            console.log(res.data.id);
                        }else{
                            layer.msg(res.msg)
                        }
                    },
                    errors: function(msg){
                        console.log(msg);
                    }
                })
            }
        })
        fileUpload("#drop_area","#photos",0);
        fileUpload("#drop_area1","#videos",1,'video/*');
        fileUpload("#drop_area3","#edu_cert_imgs",0);
        fileUpload("#drop_area4","#edu_auth_imgs",0);

        $(".del_file_btn").click(function (event) {
            $(this).hide();
            var par = $(this).prev().remove();
            $(this).parent().prepend('<div id="preview"><img src="/static/admin/plugins/img-upload/img/upload.png" class="img-responsive"  style="width: 100%;height: auto;border-radius: 4px;border: 1px solid #eee;" alt="" title=""></div>');
            $(this).parent().parent().find("input").val('');
            $("#plugeins_uploadFiles").val('');
            event.stopPropagation();
        });
        $("#email").blur(function () {
            var email = $.trim($(this).val());
            if(email.length > 1){
                $.post("{{url('/api/index/isMemberExist')}}",{'email':email},function (res) {
                    if(res.code == 200){
                        if(res.data.flg == true){
                            $("#email").css('border-color','#ef5350');
                            layer.msg('用户邮箱已存在,请修改用户邮箱');
                        }else{
                            $("#email").css('border-color','#ced4da');
                        }
                    }else{
                        layer.msg(res.msg);
                    }
                })
            }
        });
        $(document).ready(function () {
        $("#submitForm").click(function () {
            if ($.trim($("#email").val()).length == 0) {
                layer.msg('请输入邮箱', {time: 1000})
                return false;
            }
            if ($.trim($("#nick_name").val()).length == 0) {
                layer.msg('请输入外教昵称', {time: 1000})
                return false;
            }
            if ($.trim($("#name").val()).length == 0) {
                layer.msg('请输入外教 First Name', {time: 1000})
                return false;
            }
            if ($.trim($("#working_city").val()).length == 0) {
                layer.msg('期望工作地不能为空', {time: 1000})
                return false;
            }
            if ($.trim($("#photos").val()).length == 0) {
                layer.msg('请上传照片', {time: 1000})
                return false;
            }
            if ($.trim($("#videos").val()).length == 0) {
                layer.msg('请上传视频', {time: 1000})
                return false;
            }
            if ($.trim($("#notes").val()).length == 0) {
                layer.msg('请上传简历', {time: 1000})
                return false;
            }
            layer.confirm('提交信息后，信息将被企业查看并搜索到。</br>请仔细审核填写资料,确认提交？', {
                btn: ['提交','我在看看'] //按钮
            }, function(){
                $("#dataForm").submit();
            });
        });
    });
    </script>
@endsection


