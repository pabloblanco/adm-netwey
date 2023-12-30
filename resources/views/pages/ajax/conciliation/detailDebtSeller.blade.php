<h5 class="p-b-10"> Usuario: <b> {{$bankUser->name}} {{$bankUser->last_name}} </b> </h5>
<h5 class="p-b-10"> Código: <b> {{$bankUser->id_deposit}} </b> </h5>
<div class="col-md-12">
    @php
        $total = 0;
    @endphp
    @foreach($sellers as $seller)
    @if(!empty($seller->sales) && count($seller->sales))
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                {{-- <header>Registro de recepción de dinero n° {{$detail->id}}</header>
                <hr> --}}

                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Vendedor: <span> {{$seller->name}} {{$seller->last_name}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Email: <span> {{$seller->email}} </span>
                        </label>
                    </div>
                    {{--<div class="col-md-12">
                        <label class="pull-left">
                            Fecha recepci&oacute;n: <span> {{$detail->date_reg}} </span>
                        </label>
                    </div>--}}

                    <div class="col-md-12">
                        <label class="pull-left">
                            <a href="#" class="seeSales" data-sale="{{$seller->dni}}"> Ver ventas </a>
                        </label>
                    </div>

                    {{--<div class="col-md-12">
                        <label class="pull-right">
                            Monto: <span> ${{number_format($detail->amount,2,'.',',')}} </span>
                        </label>
                    </div>--}}

                    @foreach($seller->sales as $sale)
                        <div class="card card-outline-success text-dark m-b-10 {{$seller->dni}}" hidden="true">
                            <div class="card-block">
                                <header>Venta <b>{{$sale->unique_transaction}}</b></header>
                                <hr>

                                <div class="row">
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            DN Netwey: <span> {{$sale->msisdn}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Producto: <span> {{$sale->arti}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Tipo de linea: <span> {{$sale->sale_type == 'T' ? 'Telefonía' : ($sale->sale_type == 'M' ? 'MIFI' : 'Internet Hogar')}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Pack: <span> {{$sale->pack}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Fecha venta: <span> {{$sale->date_reg}} </span>
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
                </div>
            </div>
        </div>
    @endif
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($total,2,'.',',')}} </span></label>
    </div>
</div>