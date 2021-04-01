<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bootstrap 实例 - 默认的导航栏</title>
    <link rel="stylesheet" href="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://cdn.staticfile.org/jquery/2.1.1/jquery.min.js"></script>
    <script src="https://cdn.staticfile.org/twitter-bootstrap/3.3.7/js/bootstrap.min.js"></script>
</head>
<body style="padding: 200px;">
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header bg-info">
                <h4 class="m-b-0 text-white" id="tar-title"></h4>
            </div>

            <div class="card-body">
                <form action="https://gushici.liangjucai.com/admin/course/add" class="form" method="post"  id="dataForm">
                    <input type="hidden" value="bYNxNVNvmAkl14vo00BgrAq9zSAxVr59d9bzjMYl" name="_token">

                    <div class="form-group row">
                        <label for="example-text-input" class="col-2 col-form-label text-right">标题：</label>
                        <div class="col-8">
                            <input type="text" name="name" id="name" placeholder="标题" class="form-control">
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="example-text-input" class="col-2 col-form-label text-right">图片：</label>
                        <div class="col-8">
                            <input type="file"  id="up_img_txt">
                        </div>
                    </div>


                    <div class="form-actions">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="offset-sm-1col-md-8">

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
<script src='https://libs.baidu.com/jquery/1.9.1/jquery.min.js'></script>
<script>
    $(function(){
        $("#up_img_txt").change(function(){
            var formData = new FormData();
            formData.append ("file" , document.getElementById("up_img_txt").files[0]);
            $.ajax({
                type: 'POST',
                url: 'http://www.project.test/api/index/upload' ,
                data: formData ,
                processData:false,
                contentType: false,
                cache: false,
                success:function(data){
                    console.log(data);
                    if(data.status && data.message == 200){
                        console.log(data.message.data);
                    }
                },
            });

        });

        function getObjectURL(file)
        {
            var url = null ;
            if (window.createObjectURL!=undefined)
            { // basic
                url = window.createObjectURL(file) ;
            }
            else if (window.URL!=undefined)
            {
                // mozilla(firefox)
                url = window.URL.createObjectURL(file) ;
            }
            else if (window.webkitURL!=undefined) {
                // webkit or chrome
                url = window.webkitURL.createObjectURL(file) ;
            }
            return url ;
        }
    })


</script>

</body>
</html>