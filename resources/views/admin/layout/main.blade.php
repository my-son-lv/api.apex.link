<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
{{--    <link rel="stylesheet" type="text/css" href="//unpkg.com/iview/dist/styles/iview.css">--}}
{{--    <script type="text/javascript" src="//vuejs.org/js/vue.min.js"></script>--}}
{{--    <script type="text/javascript" src="//unpkg.com/iview/dist/iview.min.js"></script>--}}
{{--    <script src="//unpkg.com/axios/dist/axios.min.js"></script>--}}
    <!-- Favicon icon -->
{{--    <link rel="icon" type="image/png" sizes="16x16" href="/static/admin/images/favicon.png">--}}
    <title>@yield('title')</title>
    <!-- Bootstrap Core CSS -->
    <link href="/static/admin/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="/static/admin/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet">
    <!-- Page CSS -->
    <link href="/static/admin/css/pages/contact-app-page.css" rel="stylesheet">
    <link href="/static/admin/css/pages/footable-page.css" rel="stylesheet">
    <!--c3 CSS -->
    <link href="/static/admin/plugins/c3-master/c3.min.css" rel="stylesheet">
    <!--Toaster Popup message CSS -->
    <link href="/static/admin/plugins/toast-master/css/jquery.toast.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/static/admin/css/style.css" rel="stylesheet">

    <!-- You can change the theme colors from here -->
    <link href="/static/admin/css/colors/blue.css" id="theme" rel="stylesheet">
    <!--分页css-->
    <link href="/static/admin/plugins/footable/css/footable.bootstrap.min.css" id="theme" rel="stylesheet">


    @section('css')

    @show
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body class="fix-header fix-sidebar card-no-border">
<input type="hidden" value="@yield('menu-check')" id="menu-check" >
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="loader">
        <div class="loader__figure"></div>
        <p class="loader__label">Admin Pro</p>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<div id="main-wrapper">
    <!-- ============================================================== -->
    <!-- Topbar header - style you can find in pages.scss -->
    <!-- ============================================================== -->
    <header class="topbar">
        <nav class="navbar top-navbar navbar-expand-md navbar-light">
            <!-- ============================================================== -->
            <!-- Logo -->
            <!-- ============================================================== -->
            <div class="navbar-header">
                <a class="navbar-brand" href="{{route('admin.main')}}">
                    <!-- Logo icon --><b>
                        <!--You can put here icon as well // <i class="wi wi-sunset"></i> //-->
                        <!-- Dark Logo icon -->
                        <img src="/static/admin/images/logo-icon.png" alt="homepage" class="dark-logo" />
                        <!-- Light Logo icon -->
                        <img src="/static/admin/images/logo-light-icon.png" alt="homepage" class="light-logo" />
                    </b>
                    <!--End Logo icon -->
                    <!-- Logo text --><span>
                         <!-- dark Logo text -->
                         <img src="/static/admin/images/logo-text.png" alt="homepage" class="dark-logo" />
                        <!-- Light Logo text -->
                         <img src="/static/admin/images/logo-light-text.png" class="light-logo" alt="homepage" /></span> </a>
            </div>
            <!-- ============================================================== -->
            <!-- End Logo -->
            <!-- ============================================================== -->
            <div class="navbar-collapse">
                <!-- ============================================================== -->
                <!-- toggle and nav items -->
                <!-- ============================================================== -->
                <ul class="navbar-nav mr-auto">
                    <!-- This is  -->
                    <li class="nav-item"> <a class="nav-link nav-toggler hidden-md-up waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                    <li class="nav-item"> <a class="nav-link sidebartoggler hidden-sm-down waves-effect waves-dark" href="javascript:void(0)"><i class="ti-menu"></i></a> </li>
                </ul>
                <!-- ============================================================== -->
                <!-- User profile and search -->
                <!-- ============================================================== -->
                <ul class="navbar-nav my-lg-0">
                    <!-- ============================================================== -->
                    <!-- Language -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="flag-icon flag-icon-us"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right animated bounceInDown">
                            <a class="dropdown-item" href="">
                                <i class="flag-icon flag-icon-in"></i> 简体中文
                            </a>
                            {{--<a class="dropdown-item" href="javascript:void(0);">--}}
                                {{--<i class="flag-icon flag-icon-fr"></i> French--}}
                            {{--</a>--}}
                            {{--<a class="dropdown-item" href="javascript:void(0);">--}}
                                {{--<i class="flag-icon flag-icon-cn"></i> China--}}
                            {{--</a> <a class="dropdown-item" href="javascript:void(0);">--}}
                                {{--<i class="flag-icon flag-icon-de"></i> Dutch--}}
                            {{--</a>--}}
                        </div>
                    </li>
                    <!-- ============================================================== -->
                    <!-- Profile -->
                    <!-- ============================================================== -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle waves-effect waves-dark" href="" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img src="/static/admin/images/header.jpg" alt="user" class="profile-pic" /></a>
                        <div class="dropdown-menu dropdown-menu-right animated flipInY">
                            <ul class="dropdown-user">
                                <li>
                                    <div class="dw-user-box">
                                        <div class="u-img"><img src="/static/admin/images/header.jpg" alt="user"></div>
                                        <div class="u-text">
                                            <h4>{{session('admin_user')['name']}}</h4>
                                            <p class="text-muted">{{session('admin_user')['email']}}</p><a  class="btn btn-rounded btn-danger btn-sm">尊享VIP</a></div>
                                    </div>
                                </li>
                                {{--<li role="separator" class="divider"></li>
                                <li><a href="javascript:void(0);"><i class="ti-user"></i> My Profile</a></li>
                                <li><a href="javascript:void(0);"><i class="ti-wallet"></i> My Balance</a></li>
                                <li><a href="javascript:void(0);"><i class="ti-email"></i> Inbox</a></li>--}}
                                <li role="separator" class="divider"></li>
                                <li><a href="javascript:void(0);" data-toggle="modal" data-target="#exampleModal" data-whatever="@mdo"><i class="ti-settings"></i>  修改密码</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="{{route('admin.logout')}}"><i class="fa fa-power-off"></i>  退出登录</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- ============================================================== -->
    <!-- End Topbar header -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    @include("admin.layout.menu")
    <!-- ============================================================== -->
    <!-- End Left Sidebar - style you can find in sidebar.scss  -->
    <!-- ============================================================== -->
    <!-- ============================================================== -->
    <!-- Page wrapper  -->
    <!-- ============================================================== -->
    <div class="page-wrapper" style="padding-bottom: 0px;">
        <div class="container-fluid">
            @if (count($errors) > 0)
                <div class="alert alert-danger"> {{ $errors->all()[0] }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                </div>
                {{--@foreach ($errors->all() as $error)
                    <div class="alert alert-danger"> {{ $error }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                    </div>
                @endforeach--}}
            @endif
            @if(!empty(session('success')))
                <div class="alert alert-success"> {{session('success')}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                </div>
            @endif
            @if(!empty(session('error')))
                <div class="alert alert-danger"> {{session('error')}}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
                </div>
            @endif
            {{--<div class="alert alert-success">This is an example top alert. You can edit what u wish. </div>

            <div class="alert alert-success"> This is an example top alert. You can edit what u wish.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>

            <div class="alert alert-danger"> <i class="ti-user"></i> This is an example top alert. You can edit what u wish.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>
            <div class="alert alert-warning"> <img src="/static/admin/images/users/1.jpg" width="30" class="img-circle" alt="img"> This is an example top alert. You can edit what u wish.
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"> <span aria-hidden="true">×</span> </button>
            </div>--}}
            <div class="row page-titles">
                {{--<div class="col-md-5 align-self-center">
                    <h3 class="text-themecolor">@yield('title')</h3>
                </div>--}}
                <div class="col-lg-12">
                    <ol class="breadcrumb float-left">
                        <li class="breadcrumb-item"><a href="javascript:void(0)" id="mbx-1">管理后台</a></li>
                        <li class="breadcrumb-item active" id="mbx-2">首页</li>
                    </ol>
                </div>
                <div class="">
                    <button class="right-side-toggle waves-effect waves-light btn-inverse btn btn-circle btn-sm pull-right m-l-10"><i class="ti-settings text-white"></i></button>
                </div>
            </div>


            @section('content')

            @show
            <!-- .right-sidebar -->
            <div class="right-sidebar">
                <div class="slimscrollright">
                    <div class="rpanel-title"> 主题颜色 <span><i class="ti-close right-side-toggle"></i></span></div>
                    <div class="r-panel-body">
                        <ul id="themecolors" class="m-t-20">
                            <li><b>With Light sidebar</b></li>
                            <li><a href="javascript:void(0)" data-theme="default" class="default-theme">1</a></li>
                            <li><a href="javascript:void(0)" data-theme="green" class="green-theme">2</a></li>
                            <li><a href="javascript:void(0)" data-theme="red" class="red-theme">3</a></li>
                            <li><a href="javascript:void(0)" data-theme="blue" class="blue-theme working">4</a></li>
                            <li><a href="javascript:void(0)" data-theme="purple" class="purple-theme">5</a></li>
                            <li><a href="javascript:void(0)" data-theme="megna" class="megna-theme">6</a></li>
                            <li class="d-block m-t-30"><b>With Dark sidebar</b></li>
                            <li><a href="javascript:void(0)" data-theme="default-dark" class="default-dark-theme ">7</a></li>
                            <li><a href="javascript:void(0)" data-theme="green-dark" class="green-dark-theme">8</a></li>
                            <li><a href="javascript:void(0)" data-theme="red-dark" class="red-dark-theme">9</a></li>
                            <li><a href="javascript:void(0)" data-theme="blue-dark" class="blue-dark-theme">10</a></li>
                            <li><a href="javascript:void(0)" data-theme="purple-dark" class="purple-dark-theme">11</a></li>
                            <li><a href="javascript:void(0)" data-theme="megna-dark" class="megna-dark-theme ">12</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- End Right sidebar -->
        </div>
    </div>


    <!-- ============================================================== -->
    <!-- End Page wrapper  -->
    <!-- ============================================================== -->
</div>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->

<div class="modal fade" id="exampleModal" tabindex="-1" style="z-index: 9999;" role="dialog" aria-labelledby="exampleModalLabel1">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="exampleModalLabel1">修改密码</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form>
                    <div class="form-group row">
                        <label for="example-text-input" class="col-3 col-form-label text-right">旧密码：</label>
                        <div class="col-8">
                            <input type="password" name="oldPwd" id="oldPwd" placeholder="旧密码" class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="example-text-input" class="col-3 col-form-label text-right">新密码：</label>
                        <div class="col-8">
                            <input type="password" name="newPwd1" id="newPwd1" placeholder="新密码" class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="example-text-input" class="col-3 col-form-label text-right">确认密码：</label>
                        <div class="col-8">
                            <input type="password" name="newPwd2" id="newPwd2" placeholder="确认密码" class="form-control">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-info" id="upPassword">确认</button>
            </div>
        </div>
    </div>
</div>

<script src="/static/admin/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap popper Core JavaScript -->
<script src="/static/admin/plugins/bootstrap/js/popper.min.js"></script>
<script src="/static/admin/plugins/bootstrap/js/bootstrap.min.js"></script>
<!-- slimscrollbar scrollbar JavaScript -->
<script src="/static/admin/js/perfect-scrollbar.jquery.min.js"></script>
<!--Wave Effects -->
<script src="/static/admin/js/waves.js"></script>
<!--Menu sidebar -->
<script src="/static/admin/js/sidebarmenu.js"></script>
<!--Custom JavaScript -->
<script src="/static/admin/js/custom.min.js"></script>
<!--分页 JavaScript -->
<script src="/static/admin/plugins/moment/moment.js" type="text/javascript"></script>
<script src="/static/admin/plugins/footable/js/footable.min.js"></script>
<!-- 主题 JavaScript -->
<script src="/static/admin/plugins/styleswitcher/jQuery.style.switcher.js"></script>
<!-- 弹出框 JavaScript -->
<script src="/static/admin/plugins/layer/layer.js"></script>
<script>
$(document).ready(function () {
    $("#tar-title").text($(document).attr("title"));
    $('#exampleModal').on('hidden.bs.modal', function () {
        $("#oldPwd").val('');
        $("#newPwd1").val('');
        $("#newPwd2").val('');
    });
    $("#upPassword").click(function () {
        if($.trim($("#oldPwd").val()).length == 0){
            layer.msg('请输入旧密码',{time:1000})
            return false;
        }
        if($.trim($("#newPwd1").val()).length == 0){
            layer.msg('请输入新密码',{time:1000})
            return false;
        }
        if($.trim($("#newPwd2").val()).length == 0){
            layer.msg('请输入确认密码',{time:1000})
            return false;
        }
        if($.trim($("#newPwd1").val()) != $.trim($("#newPwd2").val())){
            layer.msg('两次密码不一致',{time:1000})
            return false;
        }
        var index = layer.load(1, {
            shade: [0.1,'#fff']
        })
        $.post("{{route('admin.user.upPwd.white')}}",{
            'old_pwd':$.trim($("#oldPwd").val()),
            'new_pwd':$.trim($("#newPwd1").val()),
            '_token':"{{csrf_token()}}"
        },function(res){
            layer.close(index);
            if(res.code == 200){
                layer.msg('密码修改成功',{time:1000});
                $("#oldPwd").val('');
                $("#newPwd1").val('');
                $("#newPwd2").val('');
                $('#exampleModal').modal('hide');
            }else{
                layer.msg(res.msg,{time:1000});
            }
        },"json")
    });
});

</script>
@section('js')

@show
</body>

</html>