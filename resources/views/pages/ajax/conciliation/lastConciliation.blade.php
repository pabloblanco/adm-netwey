<div class="col-md-12">
    @php
        $total = 0;
    @endphp
    @foreach($sales as $sale)
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <header>C&oacute;digo de recepci&oacute;n de dinero {{$sale->id_report}}</header>
                <hr>

                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Venta: <span> {{$sale->unique_transaction}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Cliente: <span> {{$sale->name}} {{$sale->last_name}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            DN Netwey: <span> {{$sale->msisdn}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Producto: <span> {{$sale->artic}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Pack: <span> {{$sale->pack}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Fecha venta: <span> {{$sale->date_reg_alt}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Cuota: <span> {{$sale->n_quote}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Fecha recepci&oacute;n de dinero: <span> {{$sale->date_acept}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Monto: <span> $ {{$sale->amount}} </span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        @php
            $total += $sale->amount;
        @endphp
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($total,2,'.',',')}} </span></label>
    </div>
</div>