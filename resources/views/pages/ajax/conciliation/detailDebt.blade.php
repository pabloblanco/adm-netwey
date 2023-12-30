@php
    $typesS = [
        'T' => 'Telefonía',
        'H' => 'Internet Hogar',
        'M' => 'MIFI',
        'MH' => 'MIFI Huella altan',
        'F' => 'Fibra'
    ]
@endphp
<h5 class="p-b-10"> Usuario: <b> {{$bankUser->name}} {{$bankUser->last_name}} </b> </h5>
<h5 class="p-b-10"> Código: <b> {{$bankUser->id_deposit}} </b> </h5>
<div class="col-md-12">
    @php
        $total = 0;
    @endphp
    @foreach($details as $detail)
        <div class="card card-outline-primary text-center text-dark m-b-10">
            <div class="card-block">
                <header>Registro de recepción de dinero n° {{$detail->id}}</header>
                <hr>

                <div class="row">
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Vendedor: <span> {{$detail->name}} {{$detail->last_name}} </span>
                        </label>
                    </div>
                    <div class="col-md-12 pull-left">
                        <label class="pull-left">
                            Email: <span> {{$detail->email}} </span>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Fecha recepci&oacute;n: <span> {{$detail->date_reg}} </span>
                        </label>
                    </div>

                    <div class="col-md-12">
                        <label class="pull-left">
                            <a href="#" class="seeSales" data-sale="{{$detail->id}}"> Ver ventas </a>
                        </label>
                    </div>

                    <div class="col-md-12">
                        <label class="pull-right">
                            Monto: <span> ${{number_format($detail->amount,2,'.',',')}} </span>
                        </label>
                    </div>

                    @foreach($detail->salesDetail as $saleDetail)
                        <div class="card card-outline-success text-dark m-b-10 {{$detail->id}}" hidden="true">
                            <div class="card-block">
                                <header>Venta <b>{{$saleDetail->unique_transaction}}</b></header>
                                <hr>

                                <div class="row">
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            DN Netwey: <span> {{$saleDetail->msisdn}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Producto: <span> {{$saleDetail->arti}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Tipo de linea: <span> {{$typesS[$saleDetail->sale_type] ?? 'Otro'}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Pack: <span> {{$saleDetail->pack}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Fecha venta: <span> {{$saleDetail->date_reg}} </span>
                                        </label>
                                    </div>
                                    <div class="col-md-12 pull-left">
                                        <label class="pull-left">
                                            Monto: <span> $ {{$saleDetail->amount}} </span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        @php
            $total += $detail->amount;
        @endphp
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($total,2,'.',',')}} </span></label>
    </div>
</div>