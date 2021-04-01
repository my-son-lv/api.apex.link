@extends('admin.layout.main')
@section('title', '企业推广')
@section('menu-check','企业推广')

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
                            <form class="form-inline" action="{{route('admin.publicity.index')}}" method="get">
                                <div class="row">
                                    <div class="form-group col-lg-5 m-t-15">
                                        <label for="input" class="form-control-label col-lg-4">姓名</label>
                                        <input type="text" name="name" class="form-control col-lg-8" placeholder="姓名"
                                               value="{{$name}}">
                                    </div>

                                    <div class="form-group col-lg-5 m-t-15">
                                        <label for="input" class="form-control-label col-lg-4">手机</label>
                                        <input type="text" name="phone" class="form-control col-lg-8" placeholder="手机"
                                               value="{{$phone}}">
                                    </div>


                                    <div class="form-group col-lg-2 m-t-15">
                                            <button type="submit"
                                                    class="btn waves-effect waves-light btn-block btn-info">搜索
                                            </button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>


                    <div class="table-responsive-sm m-t-30">
                        <a type="button" class="btn btn-info m-b-15 m-t-15"  style="color: #fff;" href="{{route('admin.publicity.add')}}"> 添加</a>
                        <table id="demo-foo-addrow"
                               class="table footable footable-paging footable-paging-right table mb-0">
                            <thead>
                            <tr class="footable-header">
                                <th class="footable-first-visible" style="display: table-cell;">ID</th>
                                <th style="display: table-cell;">姓名</th>
                                <th style="display: table-cell;">手机</th>
                                <th style="display: table-cell;">备注</th>
                                <th style="display: table-cell;" class="text-center">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($list as $k => $v)
                                <tr data-id="{{$v->id}}">
                                    <td class="footable-first-visible" style="display: table-cell;">{{$v->id}}</td>
                                    <td style="display: table-cell;">{{$v->name}}</td>
                                    <td style="display: table-cell;">{{$v->phone}}</td>
                                    <td style="display: table-cell;">{{$v->memo}}</td>
                                    <td style="display: table-cell;" class="text-center">
                                        <a type="button" class="btn btn-info btn-xs" id="edit" style="color: #fff;" href="{{route('admin.publicity.edit',['id' => $v->id])}}">
                                            <i class="far fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-info btn-danger btn-xs view-user">
                                            <i class=" fas fa-search"></i>
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

    <div id="verticalcenter" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="vcenter" style="display: block;display: none;" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="vcenter">查看</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <div >
                        <div style="text-align: center">
                            <img src="" id="view_img">
                        </div>

                        <div class="form-group row mt-4">
                            <label for="example-text-input" class="col-2 col-form-label ">链接：</label>
                            <div class="col-9" id="view_url" style="line-height: 2.5;background: #eee;padding: 0;text-align: center;border: 1px solid #e8e8e8;border-radius: 4px;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect" data-dismiss="modal">关闭</button>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>

@endsection

@section('js')
<script>
    $(function () {
        $(".view-user").click(function (e) {
            var id = $(this).parent().parent().children("td").eq(0).text();
            var url = "{{config('app.company_url').'/#/login?code='}}" + id;
            $.post("{{url('api/createQrCode')}}",{'url' : url},function (res) {
                $("#view_img").attr('src',res.data.img);
                $("#view_url").text(url);
                $("#verticalcenter").modal('show');
            })

        });
    })
</script>
@endsection


