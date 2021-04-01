<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- Tell the browser to be responsive to screen width -->
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <!-- Favicon icon -->
    <link rel="icon" type="image/png" sizes="16x16" href="/static/admin/images/favicon.png">
    <title>快捷录入系统</title>
    <!-- Bootstrap Core CSS -->
    <link href="/static/admin/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <!-- page css -->
    <link href="/static/admin/css/pages/login-register-lock.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/static/admin/css/style.css" rel="stylesheet">

    <!-- You can change the theme colors from here -->
    <link href="/static/admin/css/colors/default-dark.css" id="theme" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="/static/admin/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="/static/admin/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>

<body>
<!-- ============================================================== -->
<!-- Preloader - style you can find in spinners.css -->
<!-- ============================================================== -->
<div class="preloader">
    <div class="loader">
        <div class="loader__figure"></div>
        <p class="loader__label">Admin</p>
    </div>
</div>
<!-- ============================================================== -->
<!-- Main wrapper - style you can find in pages.scss -->
<!-- ============================================================== -->
<section id="wrapper" class="login-register login-sidebar"
         style="background-image:url(/static/admin/images/background/login-register.jpg);">
    <div class="login-box card">
        <div class="card-body">
            <form class="form-horizontal form-material" style="padding-top: 100px;">
                <a href="javascript:void(0)" class="text-center db"><img src="/static/admin/images/logo-icon.png"
                                                                         alt="Home"/><br/><img
                            src="/static/admin/images/logo-text.png" alt="Home"/></a>
                <div class="form-group m-t-40">
                    <div class="col-xs-12">
                        <input class="form-control" type="text" id="account" required="" placeholder="请输入账号">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" type="password" id="password" required="" placeholder="请输入密码">
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-12">
                        <div class="checkbox checkbox-primary float-left p-t-0">
                            <input id="checkbox-signup" type="checkbox" class="filled-in chk-col-light-blue">
                            {{--<label for="checkbox-signup"> Remember me </label>--}}
                        </div>
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase btn-rounded" id="login" type="button"> 登  录 </button>
                </div>
            </form>
        </div>
    </div>
</section>
<!-- ============================================================== -->
<!-- End Wrapper -->
<!-- ============================================================== -->
<!-- ============================================================== -->
<!-- All Jquery -->
<!-- ============================================================== -->
<script src="/static/admin/plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap tether Core JavaScript -->
<script src="/static/admin/plugins/bootstrap/js/popper.min.js"></script>
<script src="/static/admin/plugins/bootstrap/js/bootstrap.min.js"></script>
<!--Custom JavaScript -->
<!-- 弹出框 JavaScript -->
<script src="/static/admin/plugins/layer/layer.js"></script>
<script type="text/javascript">
    $(function () {
        $(".preloader").fadeOut();
    });
    $(function () {
        $('[data-toggle="tooltip"]').tooltip()
    });
    $("#login").click(function () {
        if($("#account").val().length == 0){
            layer.msg('请填写用户账号!');
            return false;
        }
        if($("#password").val().length == 0){
            layer.msg('请填写用户密码!');
            return false;
        }
        var index = layer.load(1, {
            shade: [0.3, '#fff'] //0.1透明度的白色背景
        });
        $.post("{{route('admin.login')}}",{'account':$("#account").val(),'password':$("#password").val(),'_token':"{{ csrf_token() }}"},function (res) {
            if(res.code == 200){
                location.href = "{{route('admin.main')}}";
            }else{
                layer.msg(res.msg);
            }
            layer.close(index);
        },"json")

    })
</script>

</body>

</html>