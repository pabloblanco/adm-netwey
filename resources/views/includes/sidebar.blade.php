
        <!-- Left navbar-header -->
        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse slimscrollsidebar">
                <ul class="nav" id="side-menu">
                    <li class="sidebar-search hidden-sm hidden-md hidden-lg">
                        <!-- input-group -->
                        <div class="input-group custom-search-form">
                            <input type="text" class="form-control" placeholder="Search">
                            <span class="input-group-btn">
                            <button class="btn btn-default" type="button"> <i class="fa fa-search"></i> </button>
                            </span>
                        </div>
                        <!-- /input-group -->
                    </li>
                    
                    <li class="nav-small-cap m-t-10">--- Menú principal</li>
                    @foreach ($sidebarConfig as $item)
                        @if(!empty($item))
                        <li>
                            @if(count($item->childs) == 0)
                                <a onclick="getview('{{ $item->url }}');" class="waves-effect active">
                                    <i class="linea-icon linea-basic fa-fw" data-icon="v"></i> {{ $item->description }}
                                </a>
                            @else
                                <a class="waves-effect">
                                    <i class="linea-icon linea-basic fa-fw" data-icon="7"></i> 
                                    <span class="hide-menu"> {{ $item->description }} <span class="fa fa-sort-desc"></span></span>
                                </a>
                                @foreach ($item->childs as $child)
                                    <ul class="nav nav-second-level">
                                        <li>
                                            <a class="waves-effect" href="javascript:void(0);" onclick="getview('{{ $child->url }}');">
                                                {{ $child->description }}
                                            </a>
                                        </li>
                                    </ul>
                                @endforeach
                            @endif
                        </li>
                        @endif
                    @endforeach
                </ul>
            </div>
        </div>