@extends('admin.layout.main')
@section('menu-check','菜单管理')
@section('title', '菜单添加')

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
                    <form action="{{route('admin.menu.add')}}" class="form" method="post" id="dataForm">
                        <input type="hidden" value="{{csrf_token()}}" name="_token">
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">名称：</label>
                            <div class="col-8">
                                <input type="text" name="name" placeholder="菜单名称" id="name" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">上级：</label>
                            <div class="col-8">
                                <input type="text" placeholder="顶级菜单" onclick="showMenu();" id="pname" class="form-control" readonly>
                                <input type="hidden" id="pid" name="pid" value="0">
                                <div id="menuContent" class="btn-white dropdown-toggle"
                                     style="display:none; position: absolute;z-index:999;border-radius: 3px;background: white;border: 1px solid #eee;width: 95%;">
                                    <ul id="treeDemo" class="ztree col-sm-11"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">路由：</label>
                            <div class="col-8">
                                <input type="text" name="route" id="route" placeholder="admin.user.index" class="form-control">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="example-text-input" class="col-2 col-form-label text-right">排序：</label>
                            <div class="col-8">
                                <input type="text" name="sort" placeholder="排序" id="sort" class="form-control">
                            </div>
                        </div>
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
    <!--ztree start-->
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.core.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.excheck.js"></script>
    <script type="text/javascript" src="/static/admin/plugins/ztree/jquery.ztree.exedit.js"></script>
<script>
    var setting = {view: {
            dblClickExpand: false
        },
        data: {
            simpleData: {
                enable: true
            }
        },
        callback: {
            beforeClick: beforeClick,
            onClick: onClick
        }
    };

    function beforeClick(treeId, treeNode) {
        var check = (treeNode);
        $("#pid").val(check.id);
        hideMenu();
        return check;
    }

    function onClick(e, treeId, treeNode) {
        var zTree = $.fn.zTree.getZTreeObj("treeDemo"),
            nodes = zTree.getSelectedNodes(),
            v = "";
        nodes.sort(function compare(a, b) {
            return a.id - b.id;
        });
        for (var i = 0, l = nodes.length; i < l; i++) {
            v += nodes[i].name + ",";
        }
        if (v.length > 0) v = v.substring(0, v.length - 1);
        var cityObj = $("#pname");
        cityObj.attr("value", v);
    }

    function showMenu() {
        var cityObj = $("#pname");
        var cityOffset = $("#pname").offset();
        $("#menuContent").css("0px", "0px").slideDown("fast");

        $("body").bind("mousedown", onBodyDown);
    }

    function hideMenu() {
        $("#menuContent").fadeOut("fast");
        $("body").unbind("mousedown", onBodyDown);
    }

    function onBodyDown(event) {
        if (!(event.target.id == "menuBtn" || event.target.id == "menuContent" || $(event.target).parents("#menuContent").length > 0)) {
            hideMenu();
        }
    }
    $(document).ready(function () {
        $("#submitButton").click(function () {
            if($.trim($("#name").val()).length == 0){
                layer.msg('请输入菜单名称',{time:1000})
                return false;
            }
            if($("#pid").val().length == 0){
                layer.msg('请选择上级菜单',{time:1000})
                return false;
            }
            $("#dataForm").submit();
        });

        var zNodes = [];
        $.ajax({
            url: "{{route('admin.menu.menuList.white')}}",
            type: "POST",
            dataType: "json",
            async: false,
            data: {"_token": "{{csrf_token()}}",'type':1},
            success: function (res) {
                zNodes = res;
            }
        });
        $.fn.zTree.init($("#treeDemo"), setting, zNodes);
    });
</script>
@endsection


