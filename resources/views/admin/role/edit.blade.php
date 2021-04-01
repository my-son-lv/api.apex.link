@extends('admin.layout.main')
@section('menu-check','角色管理')
@section('title', '角色编辑')

@section('css')
    <!--css调用-->
    <!--ztree start-->
    <link rel="stylesheet" href="/static/admin/plugins/ztree/bootstrapStyle/bootstrapStyle.css" type="text/css">

@endsection

@section('content')

    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title"></h4>
                </div>

                <div class="card-body">
                    <form action="{{route('admin.role.edit')}}" class="form" method="post"  id="dataForm">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">
                        <input type="hidden" value="{{$model->id}}" name="id">

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">名称：</label>
                            <div class="col-8">
                                <input type="text" name="name" placeholder="角色名称" class="form-control"
                                       value="{{$model->name}}" id="name">
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">状态：</label>
                            <div class="col-8" style="line-height: 38px;">
                                <input name="status" type="radio" id="radio_1" value="0"
                                       @if($model->status == 0) checked="" @endif class="radio-col-light-blue">
                                <label for="radio_1" style="min-width: 80px;">启用</label>
                                <input name="status" type="radio" id="radio_2" value="1"
                                       @if($model->status == 1) checked="" @endif class="radio-col-light-blue">
                                <label for="radio_2">禁用</label>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">操作：</label>
                            <div class="col-8">
                                <ul id="treeDemo" class="ztree"></ul>
                            </div>
                            <input type="hidden" id="menu_list" name="menu_list">
                        </div>

                        <div class="form-actions">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="offset-sm-2 col-md-8">
                                            {{--<button type="button" id="goBack" class="btn btn-inverse m-l-10"><i
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
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.core.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.excheck.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.exedit.js"></script>
    <script>
        $(document).ready(function(){
            var setting = {check: {enable: true}, data: {simpleData: {enable: true}},};
            var zNodes = [];
            $.post({url:"{{route('admin.menu.menuList.white')}}",type:"POST",async:false,dataType:'json',data:{'_token' : "{{csrf_token()}}",'type':1,id:"{{$model->id}}"},success:function(res){zNodes = res;}});
            $.fn.zTree.init($("#treeDemo"), setting, zNodes);
            $("#submitForm").click(function () {
                if($.trim($("#name").val()).length == 0){
                    layer.msg('请输入角色名称',{time:1000});
                    return false;
                }
                var zTreeOjb = $.fn.zTree.getZTreeObj("treeDemo");
                var nodes = zTreeOjb.getCheckedNodes();
                if(nodes.length==0){
                    layer.msg("请选择可以操作的菜单",{time:1000});
                    return false;
                }
                var str = '';
                for(var i in  nodes){str+=nodes[i].id + ",";}
                $("#menu_list").val(str.substr(0,str.length-1));
                $("#dataForm").submit();
            });

        });
    </script>
@endsection


