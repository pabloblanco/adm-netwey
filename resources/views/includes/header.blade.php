<!-- Navigation -->
<nav class="navbar navbar-default navbar-static-top m-b-0">
    <div class="navbar-header"> <a class="navbar-toggle hidden-sm hidden-md hidden-lg " href="javascript:void(0)" data-toggle="collapse" data-target=".navbar-collapse"><i class="ti-menu"></i></a>
        <ul class="nav navbar-top-links navbar-left hidden-xs">
            <li><a href="javascript:void(0)" class="open-close hidden-xs waves-effect waves-light"><i class="icon-arrow-left-circle ti-menu"></i></a></li>
        </ul>

        <ul class="nav navbar-top-links navbar-right pull-right">
            <li class="dropdown">
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <b class="hidden-xs">{{session('user.email')}}</b>
                </a>
                <ul class="dropdown-menu dropdown-user animated flipInY">
                    <li>
                        <a href="{{route("logout")}}"><i class="fa fa-power-off"></i> Logout</a>
                    </li>
                </ul>
            </li>

            {{-- Notificaciones --}}
            <li class="dropdown noti-report" hidden="true">
                <a class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#">
                    <i class="ti-check-box"></i>
                    <div class="notify new-report" hidden="true">
                        <span class="heartbit"></span>
                        <span class="point"></span>
                    </div>
                </a>

                <ul class="dropdown-menu mailbox animated bounceInDown" style="max-height: 585px;">
                    <li>
                        <div class="drop-title"></div>
                    </li>
                    
                    <li>
                        <div class="message-center" id="notification-content"></div>
                    </li>

                    {{--<li>
                        <a class="text-center" href="#">
                            <strong>Ver todas</strong>
                            <i class="fa fa-angle-right"></i>
                        </a>
                    </li>--}}
                </ul>
                <!-- /.dropdown-messages -->
            </li>
        </ul>
        <div class="top-left-part">
            <a class="logo" href="{{route('root')}}">
                <b>
                    <span class="hidden-xs">
                        <img src="logo_header.png" alt="home" />
                    </span>
                </b>
            </a>
        </div>
    </div>
</nav>