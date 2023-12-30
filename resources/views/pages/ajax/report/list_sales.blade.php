<h5 class="p-b-10"> Recepcion de efectivo id: <b> {{ $id }} </b> </h5>
<div class="col-md-12">
	@php
		$acum = 0;
	@endphp
	@foreach($salesDetail as $sale)
	    <div class="card card-outline-primary text-center text-dark m-b-10">
	        <div class="card-block">
	            <header>Venta {{ $sale->unique_transaction }}</header>
	            <hr>

	            <div class="row">
	                <div class="col-md-12 pull-left">
	                    <label class="pull-left">
	                        DN Netwey: <span> {{ $sale->msisdn }} </span>
	                    </label>
	                </div>
	                <div class="col-md-12 pull-left">
	                    <label class="pull-left">
	                        Producto: <span> {{ $sale->arti }} </span>
	                    </label>
	                </div>
	                <div class="col-md-12 pull-left">
	                    <label class="pull-left">
	                        Pack: <span> {{ $sale->pack }} </span>
	                    </label>
	                </div>
	                <div class="col-md-12 pull-left">
	                    <label class="pull-left">
	                        Fecha venta: <span> {{ $sale->date_reg }} </span>
	                    </label>
	                </div>
	                <div class="col-md-12 pull-left">
	                    <label class="pull-left">
	                        Monto: <span> ${{ number_format($sale->amount,2,'.',',') }} </span>
	                    </label>
	                </div>
	            </div>
	        </div>
	    </div>
	    @php
			$acum += $sale->amount;
		@endphp
    @endforeach

    <div class="col-md-12">
        <label class="pull-right">Total: <span> ${{number_format($acum,2,'.',',')}} </span></label>
    </div>
</div>