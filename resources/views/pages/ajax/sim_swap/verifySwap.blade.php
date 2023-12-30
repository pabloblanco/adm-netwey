<div class="panel panel-default block1">
    <div class="panel-wrapper collapse in">
        <div class="panel-heading"> Estatus del SIM SWAP </div>
        <div class="panel-body">
            <dl>
                <dt>Nombre</dt>
                <dd>{{$client["name"]}} {{$client["last_name"]}}</dd>
                <dt>Teléfono de contacto</dt>
                <dd>{{$client["phone_home"]}}</dd>
                <dt>Direccion</dt>
                <dd>{{$client["address"]}}</dd>
                <dt>ICCID</dt>
                <dd>{{$client["iccid"]}}</dd>
            </dl>
            @if($sim == 'OK')
                <div class="card card-inverse card-success text-center text-white">
                    <div class="card-block">
                        <p>SIM SWAP Exitoso</p>
                    </div>
                </div>
            @else
                <div class="card card-inverse card-danger text-center text-white">
                    <div class="card-block">
                        <p>SIM SWAP en proceso ...</p>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>