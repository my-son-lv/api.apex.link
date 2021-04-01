@extends('admin.layout.main')
@section('title', '用户管理')
@section('menu-check','用户管理')

@section('css')
    <!--css调用-->
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header bg-info">
                    <h4 class="m-b-0 text-white" id="tar-title"></h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-12">
                            <form class="form-inline" action="{{route('admin.user.index')}}" method="get">
                                <div class="row">
                                    <div class="form-group col-lg-3 m-t-15">
                                        <label for="input" class="form-control-label col-lg-4">姓名</label>
                                        <input type="text" name="name" class="form-control col-lg-8" placeholder="用户姓名"
                                               value="{{$name}}">
                                    </div>
                                    <div class="form-group col-lg-3 m-t-15">
                                        <label for="input" class="form-control-label col-lg-4">手机</label>
                                        <input type="text" name="phone" class="form-control col-lg-8" placeholder="手机"
                                               value="{{$phone}}">
                                    </div>
                                    <div class="form-group col-lg-3 m-t-15">
                                        <label for="input" class="form-control-label col-lg-4">状态</label>
                                        <select class="form-control col-lg-8" name="status">
                                            <option value="">全部</option>
                                            <option value="0" @if($status==='0') selected @endif>启用</option>
                                            <option value="1" @if($status==='1') selected @endif>停用</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-lg-3 m-t-15">
                                        <div class="col-lg-offset-4 col-lg-8">
                                            <button type="submit"
                                                    class="btn waves-effect waves-light btn-block btn-info">搜索
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="table-responsive-sm m-t-30">
                        <a type="button" class="btn btn-info m-b-15 m-t-15"  style="color: #fff;" href="{{route('admin.user.add')}}"> 添加</a>
                        <table id="demo-foo-addrow"
                               class="table footable footable-paging footable-paging-right table mb-0">
                            <thead>
                            <tr class="footable-header">
                                <th class="footable-first-visible" style="display: table-cell;">ID</th>
                                <th style="display: table-cell;">姓名</th>
{{--                                <th style="display: table-cell;">账号</th>--}}
                                <th style="display: table-cell;">邮箱</th>
                                <th style="display: table-cell;">手机号</th>
                                <th style="display: table-cell;">状态</th>
                                <th style="display: table-cell;">最后登录</th>
                                <th style="display: table-cell;" class="text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $k => $v)
                                <tr data-id="{{$v->id}}">
                                    <td class="footable-first-visible" style="display: table-cell;">{{$v->id}}</td>
                                    <td style="display: table-cell;">{{$v->name}}</td>
{{--                                    <td style="display: table-cell;">{{$v->account}}</td>--}}
                                    <td style="display: table-cell;">{{$v->email}}</td>
                                    <td style="display: table-cell;">{{$v->phone}}</td>
                                    <td style="display: table-cell;">
                                        @if($v->status == 0)
                                            <span class="label label-success">启用</span>
                                        @else
                                            <span class="label label-danger">停用</span>
                                        @endif
                                    </td>
                                    <td style="display: table-cell;">{{$v->last_login_time ?? '-'}}</td>
                                    <td style="display: table-cell;" class="text-center">
                                        @if($v->status == 0)
                                            <button type="button" class="btn btn-danger btn-xs"
                                                    data-status="{{$v->status}}" id="upStatusDown" >
                                                <i class="fas fa-square-full"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-success btn-xs"
                                                    data-status="{{$v->status}}" id="upStatusUp">
                                                <i class="fas fa-play"></i>
                                            </button>
                                        @endif
                                        <a type="button" class="btn btn-info btn-xs" id="edit" style="color: #fff;" href="{{route('admin.user.edit',['id' => $v->id])}}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-info btn-danger btn-xs del-user" >
                                            <i class=" far fa-trash-alt"></i>
                                        </button>

                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                            <tfoot>
                            <tr class="footable-paging" style="margin-top: 20px;">
                                <th colspan="8">
                                    <div class="footable-pagination-wrapper">
                                        {{ $list->links() }}
                                    </div>
                                </th>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('js')
<script>
    $(function () {
        $("#upStatusUp,#upStatusDown").click(function () {
            var status = $(this).attr('data-status');
            var status_val = status == 0 ? '停用' : '启用';
            var id = $(this).parent().parent().attr('data-id');
            layer.confirm('您确定要' + status_val + '？', {
                btn: ['是', '否'] //按钮
            }, function () {
                var index = layer.load(1, {
                    shade: [0.3, '#fff'] //0.1透明度的白色背景
                });
                $.post("{{route('admin.user.status')}}", {
                    'id': id,
                    '_token': "{{ csrf_token() }}",
                }, function (res) {
                    if (res.code == 200) {
                        location.reload();
                    } else {
                        layer.msg(res.msg);
                        layer.close(index)
                    }
                }, "json");
            });
        });

        $(".del-user").click(function () {
            var id = $(this).parent().parent().attr('data-id');
            layer.confirm('您确定要删除？', {
                btn: ['是', '否'] //按钮
            }, function () {
                var index = layer.load(1, {
                    shade: [0.3, '#fff'] //0.1透明度的白色背景
                });
                $.post("{{route('admin.user.del')}}", {
                    'id': id,
                    '_token': "{{ csrf_token() }}",
                }, function (res) {
                    if (res.code == 200) {
                        location.reload();
                    } else {
                        layer.msg(res.msg);
                        layer.close(index)
                    }
                }, "json");
            });

        });
    })
</script>
@endsection


