@inject('menu','App\Models\Menu')

<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div class="scroll-sidebar">
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav">
            <ul id="sidebarnav">
                {{--<li class="nav-devider"></li>--}}
                {{--<li class="nav-small-cap">PERSONAL</li>--}}
            @foreach($menu->getMenuList() as $k => $v)
                    @if($v->pid == 0 )
                        <li> <a class="has-arrow waves-effect waves-dark" href="javascript:void(0);" aria-expanded="false"><i class="{{$v->icon}}"></i><span class="hide-menu">{{$v->name}} </span></a>
                            <ul aria-expanded="false" class="collapse">
                                @foreach($menu->getMenuList()  as $k1 => $v1)
                                    @if($v1->pid == $v['id']  && $v1->pid > 0)
                                        <li><a href="{{ $v1->route ? route($v1->route) : 'javascript:void(0);'}}">{{$v1->name}}</a></li>
                                    @endif
                                @endforeach
                            </ul>
                        </li>
                    @endif
                @endforeach
            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>