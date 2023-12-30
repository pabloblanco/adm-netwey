<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <div class="row">
                <h3 class="box-title">Datos del cliente</h3>

                <div class="col-md-12">
                    @if(!empty($reshtml["client"]))
                        <ul class="list-icons" id="status-line">
                            <li>
                                <strong>Nombre:</strong>
                                <span>{{$reshtml["client"]->name}} {{$reshtml["client"]->last_name}}</span>
                            </li>
                            <li>
                                <strong>Teléfono:</strong>
                                <span>{{$reshtml["client"]->phone_home}}</span>
                            </li>
                            <li>
                                <strong>Email:</strong>
                                <span>{{$reshtml["client"]->email}}</span>
                            </li>
                        </ul>
                    @else
                        <div class="card card-inverse card-danger text-center text-white">
                            <div class="card-block">
                                <p>La orden no tiene un cliente asociado</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="white-box">
            <div class="row">

                <h3 class="box-title">Compra</h3>

                <div class="col-md-12">
                    <form class="form-horizontal" id="saveOrderForm" method="POST" action="" data-toggle="validator">
                        {{ csrf_field() }}
                        <div class="card card-outline-info text-dark">
                            <div class="card-block">
                                <ul class="list-icons" id="status-line">
                                    <li>
                                        <strong>Monto Total:</strong>
                                        <span>{{$reshtml["order"]->amount}}</span>
                                    </li>
                                    <li>
                                        <strong>Monto de envío:</strong>
                                        <span>{{$reshtml["order"]->monto_envio}}</span>
                                    </li>
                                    <li>
                                        <strong>Fecha compra:</strong>
                                        <span>{{$reshtml["order"]->date}}</span>
                                    </li>
                                    <li>
                                        <strong>Fecha pago:</strong>
                                        <span>{{$reshtml["purchase"]->date}}</span>
                                    </li>
                                </ul>

                                <h3 class="box-title"> Detalle de la compra </h3>
                                @if(!empty($reshtml["details"]))
                                    @foreach($reshtml["details"] as $detail)
                                    <div class="card card-outline-success m-b-10">
                                        <div class="card-block">
                                            <ul class="list-icons" id="status-line">
                                                <li>
                                                    <strong>Articulo:</strong>
                                                    <span>{{$detail->title}}</span>
                                                </li>
                                                <li>
                                                    <strong>Descripión:</strong>
                                                    <span>{{$detail->description}}</span>
                                                </li>
                                                <li>
                                                    <strong>SKU:</strong>
                                                    <span>{{$detail->sku}}</span>
                                                </li>
                                                <li>
                                                    <strong>Precio:</strong>
                                                    <span>{{$detail->price}}</span>
                                                </li>
                                                <li>
                                                    <strong>Servicialidad:</strong>
                                                    <span>{{$detail->serviciability}}</span>
                                                </li>
                                            </ul>

                                            <h4>Ingresar datos del dispositivo</h4>

                                            <div class="form-group col-sm-12 col-md-6">
                                                <input class="form-control" name="dn-{{$detail->id}}" type="text" placeholder="MSISDN" required>
                                            </div>

                                            <div class="form-group col-sm-12 col-md-6">
                                                <input class="form-control" name="iccid-{{$detail->id}}" type="text" placeholder="ICCID" required>
                                            </div>

                                            <div class="form-group col-sm-12 col-md-6">
                                                <input class="form-control" name="imei-{{$detail->id}}" type="text" placeholder="IMEI" required>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="card card-inverse card-danger text-center text-white">
                                        <div class="card-block">
                                            <p>La orden no tiene detalle</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-12 p-t-30">
                            <button class="btn btn-info btn-lg btn-block text-uppercase" id="save" type="button">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>