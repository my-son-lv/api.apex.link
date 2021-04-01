@extends('admin.layout.main')
@section('menu-check','企业推广')
@section('title', '推广添加')

@section('css')

@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title"></h4>
                </div>

                <div class="card-body">
                    <form action="{{route('admin.publicity.add')}}" class="form" method="post" id="dataForm">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">姓名：</label>
                            <div class="col-8">
                                <input type="text" name="name" placeholder="姓名" class="form-control" id="name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">手机：</label>
                            <div class="col-8">
                                <input type="text" name="phone" placeholder="手机" class="form-control" id="phone">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">备注：</label>
                            <div class="col-8">
                                <input type="text" name="memo" placeholder="备注" class="form-control" id="memo">
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
                                            <button id="submitForm" type="button" class="btn btn-info m-r-10"><i
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
    <!--ztree start-->
    <script>
        $(document).ready(function(){
            $("#submitForm").click(function () {
                if($.trim($("#name").val()).length == 0){
                    layer.msg('请输入姓名',{time:1000});
                    return false;
                }
                $("#dataForm").submit();
            });

        });
    </script>
@endsection


