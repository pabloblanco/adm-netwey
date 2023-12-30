<div class="col-md-12">
    @foreach($detail as $quote)
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Cuota n&uacute;mero: <span> {{ $quote->n_quote }} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Monto: <span> {{ '$'.number_format($quote->amount,2,'.',',') }} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Cociliado: <span> {{ $quote->conciliation_status == 'P' ? 'Si' : 'No' }} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Fecha de cobro: <span> {{ date('d-m-Y H:i:s', strtotime($quote->date_reg)) }} </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>