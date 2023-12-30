@if (count($sales) > 0)
	<h3>Ventas activas</h3>
	<div class="table-responsive">
		<table id="myTable" class="table table-striped">
			<thead>
				<tr>
					<th></th>
					<th>id</th>
					<th>Concentrador</th>
					<th>Cliente</th>
					<th>Paquete</th>
					<th>Servicio</th>
					<th>Producto</th>
					<th>Monto</th>
					<th>A recibir</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($sales as $sale)
				<tr>
					<td>
						<div class="form-check">
							<label class="form-check-label bt-switch" data-toggle="tooltip" data-animation="false" title="Indicar 'A recibir'">
								<input type="checkbox" id="check_{{$sale->id}}" class="form-check-input" value="0">
							</label>
						</div>
					</td>
					<td>
						{{$sale->id}}
					</td>
					<td>
						{{$sale->apikey->concentrator->name}}
					</td>
					<td>
						{{$sale->client->client->name.' '.$sale->client->client->last_name}}
					</td>
					<td>
						@if (isset($sale->pack))
							{{$sale->pack->title}}
						@else
							N/A
						@endif
					</td>
					<td>
						{{$sale->services_name}}
					</td>
					<td>
						@if (isset($sale->article))
							@if (isset($sale->article->parent))
								{{$sale->article->parent->title}}
							@else
								N/A
							@endif
						@else
							N/A
						@endif
					</td>
					<td>
						{{ number_format($sale->amount,2,'.',',') }}
						<input type="hidden" id="amount_{{$sale->id}}" value="{{$sale->amount}}">
					</td>
					<td>
						<input type="number" id="received_{{$sale->id}}" class="form-control" placeholder="Monto a recibir" value="{{$sale->amount}}" disabled>
					</td>
				</tr>
			@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="7" align="right">
						Monto total:
					</td>
					<td>
						{{ number_format($amount,2,'.',',') }}
					</td>
					<td>
						<button type="button" class="btn btn-success btn-lg button" onclick="aprove('{{ $ids }}')">Aprobar</button>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
@else
	<h3>El 
            @if (session('user')->platform == 'admin')
              coordinador
            @else
              vendedor
            @endif no tiene ventas activas</h3>
@endif
<script src="js/sellerreception/sales.js"></script>