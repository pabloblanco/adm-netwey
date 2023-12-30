<h3>Inventarios</h3>
<div class="row">
	<div class="col-md-12">
		@if (isset($inventories['inventory']))
			<div class="row">
				<div class="col-md-12">
			        <button class="btn btn-success" onclick="downloadInventorycsv();">Exportar en CSV</button>
			        <a id="download"></a>
				</div>
			</div>
			<br>
			<div class="table-responsive">
	            <table id="myTable" class="table table-striped">
					<thead>
						<tr>
							<th>Marca</th>
							<th>IMEI</th>
							<th>Telefono netwey</th>
							<th>Observaciones</th>
							<th>Precio</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($inventories['inventory'] as $product)
							<tr>
								<td>{{ $product->article}}</td>
								<td>{{ $product->imei }}</td>
								<td>{{ $product->msisdn }}</td>
								<td>{{ $product->obs }}</td>
								<td>{{ number_format($product->price_pay,2,'.',',') }}</td>
							</tr>
						@endforeach
					</tbody>
					<tfoot >
						<tr>
							<td></td>
							<td></td>
							<td></td>
							<td></td>
							<td>Deuda: {{number_format($inventories['amount'],2,'.',',')}}</td>
						</tr>
					</tfoot>
	            </table>
	      	</div>
		@else
			<p>El vendedor no tiene deudas</p>
		@endif
	</div>
</div>
@if (isset($inventories['inventory']))
	<table hidden id="reportTableInventory">
		<caption>EstatusVendedor-Inventario</caption>
		<thead>
			<tr>
				<th>Marca</th>
				<th>IMEI</th>
				<th>MSISDN</th>
				<th>Observaciones</th>
				<th>Precio</th>
				<th>Deuda</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($inventories['inventory'] as $product)
				<tr>
					<td>{{ $product->article}}</td>
					<td>{{ $product->imei }}</td>
					<td>{{ $product->msisdn }}</td>
					<td>{{ $product->obs }}</td>
					<td>{{ number_format($product->price_pay,2,'.',',') }}</td>
					<td>{{number_format($inventories['amount'],2,'.',',')}}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif

<h3>Efectivo</h3>
<div class="row">
	<div class="col-md-12">
		@if (isset($sells['sales']))
			<div class="row">
				<div class="col-md-12">
			        <button class="btn btn-success" onclick="downloadCashcsv();">Exportar en CSV</button>
			        <a id="download"></a>
				</div>
			</div>
			<br>
			<div class="table-responsive">
			  	<table id="myTablecash" class="table table-striped">
			      <thead>
			        <tr>
			          <th>Transacción única</th>
			          <th>Tipo de transacción</th>
			          <th>Fecha de la Transacción</th>
			          <th>Concentrador</th>
			          <th>Vendedor</th>
			          <th>Servicio</th>
		              <th>Cliente</th>
		              <th>Teléfono Netwey</th>
		              <th>Teléfono de contacto</th>
			          <th>Conciliado</th>
			          <th>Monto pagado</th>
			        </tr>
			      </thead>
			      <tbody>
			        @foreach ($sells['sales'] as $item)
			          <tr>
						<td>{{$item->unique_transaction}}</td>
						<td>
							@if ($item->type == 'A')
								Alta
							@elseif ($item->type == 'R')
								Recarga
							@elseif ($item->type == 'P')
								Plan inicial
							@elseif ($item->type == 'CR')
								Pago de credito
							@endif
						</td>
						<td>{{$item->date_reg}}</td>
						<td>{{$item->concentrator}}</td>
						<td>{{$item->user_name}}</td>
						<td>{{$item->service}}</td>
						<td>{{$item->msisdn}}</td>
						<td>
							@if (isset($item->client_phone))
								{{$item->client_phone}}
							@else
								N/A
							@endif
						</td>
						<td>{{$item->client_name}} {{$item->client_lname}}</td>
						<td>@if ($item->conciliation == 'Y') Si @else No @endif</td>
						<td>{{ number_format($item->amount,2,'.',',') }}</td>
			          </tr>
			        @endforeach
			      </tbody>
				<tfoot >
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td>Efectivo:</td>
						<td>{{ number_format($sells['amount'],2,'.',',') }}</td>
					</tr>
				</tfoot>
				</table>
			</div>
		@else
			<p>El vendedor no tiene efectivo en mano</p>
		@endif
	</div>
</div>
@if (isset($sells['sales']))
	<table hidden id="reportTableCash">
		<caption>EstatusVendedor-Efectivo</caption>
		<thead>
			<tr>
				<th>Transacción única</th>
				<th>Tipo de transacción</th>
				<th>Fecha de la Transacción</th>
				<th>Concentrador</th>
				<th>Vendedor</th>
				<th>Servicio</th>
				<th>Cliente</th>
				<th>Teléfono Netwey</th>
				<th>Teléfono de contacto</th>
				<th>Conciliado</th>
				<th>Monto pagado</th>
			    <th>Efectivo</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($sells['sales'] as $item)
				<tr>
					<td>{{$item->unique_transaction}}</td>
					<td>@if ($item->type == 'A') Alta @elseif ($item->type == 'R') Recarga @elseif ($item->type == 'P') Plan inicial @elseif ($item->type == 'CR') Pago de credito @endif</td>
					<td>{{$item->date_reg}}</td>
					<td>{{$item->concentrator}}</td>
					<td>{{$item->user_name}}</td>
					<td>{{$item->service}}</td>
					<td>{{$item->client_name}} {{$item->client_lname}}</td>
					<td>{{$item->msisdn}}</td>
					<td>
						@if (isset($item->client_phone))
							{{$item->client_phone}}
						@else
							N/A
						@endif
					</td>
					<td>@if ($item->conciliation == 'Y') Si @else No @endif</td>
					<td>{{ number_format($item->amount,2,'.',',') }}</td>
					<td>{{ number_format($sells['amount'],2,'.',',') }}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif
<script src="js/reports/seller_detail.js"></script>