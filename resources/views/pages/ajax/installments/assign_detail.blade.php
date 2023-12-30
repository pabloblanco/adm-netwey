<div class="col-md-12">
    <h3 class="text-center">
        Ventas Tomadas en cuenta para el calculo de m&oacute;dems
    </h3>
</div>

<div class="col-md-12 p-t-20">
    @foreach($salesD as $sale)
    <div class="col-md-4">
        <h4>
            Semama <br>
            <small>{{ $sale['date_beg'] }} A {{ $sale['date_end'] }}</small>
        </h4>
        <p><b>{{ $sale['count'] }}</b> Ventas</p>
    </div>
    @endforeach
</div>

<div class="col-md-12 p-t-20">
    <h3 class="text-center">
        Asignar m&oacute;dems
    </h3>
    @if($data->tokens_cron > 0)
        <div class="col-md-12 col-sm-12">
            <p>Total disponible para asignar: <b>{{ $data->tokens_cron }}</b> </p>
            <p>Asignados: <b id="m-assign">{{ $data->tokens_assigned }}</b> </p>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="form-group">
                <input type="number" name="tokens" id="tokens" class="form-control" placeholder="{{ $data->tokens_cron }}" min="0" max="{{ $data->tokens_cron }}">
            </div>
        </div>
        <div class="col-md-8">
            <button type="button" class="btn btn-success" id="assign" data-total="{{ $data->tokens_cron }}">Asignar</button>
        </div>
    @else
        <p>El coordinador no tiene modems para asignar.</p>
    @endif
</div>