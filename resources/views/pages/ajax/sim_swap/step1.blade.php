<div class="panel panel-default block1">
    <div class="panel-wrapper collapse in">
        <div class="panel-heading"> Datos del cliente </div>
        <div class="panel-body">
            <dl>
                <dt>Nombre</dt>
                <dd>{{$client["name"]}} {{$client["last_name"]}}</dd>
                <dt>Teléfono de contacto</dt>
                <dd>{{$client["phone_home"]}}</dd>
                <dt>Direccion</dt>
                <dd>{{$client["address"]}}</dd>
                <dt>ICCID</dt>
                <dd id="iccOrigen">{{$client["iccid"]}}</dd>
                <dt>Estatus de la linea</dt>
                <dd>{{$client["line_status"]}}</dd>
                <dt>Plan</dt>
                <dd>{{$client["plan"]}}</dd>
            </dl>

            @if($client["line_status"] == 'Suspendida')
                <div class="card card-inverse card-danger text-center text-white">
                    <div class="card-block">
                        <p>Linea suspendida</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

@if($client["line_status"] != 'Suspendida')
    <form class="form-horizontal" id="swapForm" method="POST" action="">
        {{ csrf_field() }}
        <div class="panel panel-default block1">
            <div class="panel-wrapper collapse in">
                <div class="panel-heading"> Item(s) a cambiar </div>
                <div class="panel-body">
                    <div class="radio-list">
                        <label class="radio-inline">
                            <div class="radio radio-success">
                                <input type="radio" name="typeswap" id="modem" value="modem" checked>
                                <label for="modem">Modem + SIM Card</label>
                            </div>
                        </label>
                        <label class="radio-inline">
                            <div class="radio radio-success">
                                <input type="radio" name="typeswap" id="sim" value="sim">
                                <label for="sim">SIM Card</label>
                            </div>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-default block1">
            <div class="panel-wrapper collapse in">
                <div class="panel-heading"> MSISDN Nuevo </div>
                <div class="panel-body">
                    <div class="input-group">
                        <input type="text" class="form-control" id="dnDes"  name="dnDes" placeholder="DN Destino">
                        <span class="input-group-btn">
                            <button class="btn btn-success" type="button" id="btnProD">
                                Consultar
                            </button>
                        </span>
                    </div>

                    <dl class="p-t-20" id="des-content" style="display: none;">
                        <div id="imei-content">
                            <dt>IMEI</dt>
                            <dd id="input-imei">
                                <input type="text" class="form-control" id="imei"  name="imei" placeholder="IMEI Destino">
                            </dd>
                        </div>
                        <dt>ICCID</dt>
                        <dd id="iccid"></dd>
                        <dt>Estatus</dt>
                        <dd id="status"></dd>
                    </dl>

                    <p class="text-danger" id="error-simd" style="display: none;">
                        El msisdn destino debe estar en estatus IDLE para poder hacer el SIM SWAP.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-center">
            <button class="btn btn-block btn-success" type="button" id="processSwap" disabled>Procesar</button>
        </div>
    </form>
@endif