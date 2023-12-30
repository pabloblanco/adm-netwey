<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Inventario Brightstar</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Inv. Brightstar</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        @if($error)
            <div class="card card-inverse card-danger text-center text-white">
                <div class="card-block">
                    <p>Ocurrio un error consultando el inventario.</p>
                </div>
            </div>
        @else
            @foreach($devices as $device)
                <div class="col-md-4 col-xs-12">
                    <div class="white-box">
                        <h3 class="box-title">
                            @if($device->sku == 'ALC000007') Alcatel MW41 @else {{$device->title}} @endif
                        </h3>
                        <h4 class="box-title">({{$device->desc}})</h4>
                        <ul class="list-inline two-part">
                            <li><i class="fa fa-barcode text-success"></i></li>
                            <li class="text-right"><span class="counter">{{$device->quantity}}</span></li>
                        </ul>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>