@if (count($sales) > 0)
	<h3>Ventas activas</h3>
	<div class="table-responsive">
		<table id="myTable" class="table table-striped">
			<thead>
				<tr>
					<th>Conciliar</th>
					<th>Transacción única</th>
					<th>Tipo</th>
					<th>Concentrador</th>
					<th>Cliente</th>
					<th>Paquete</th>
					<th>Servicio</th>
					<th>Producto</th>
					<th>MSISDN</th>
					<th>Monto</th>
				</tr>
			</thead>
			<tbody>
			@foreach ($sales as $sale)
				<tr>
					<td>
						<div class="form-check">
							<label class="form-check-label bt-switch" data-toggle="tooltip" data-animation="false" title="Conciliar">
								<input type="checkbox" id="check_{{$sale->id}}" class="form-check-input" value="1" checked>
							</label>
						</div>
					</td>
					<td>
						{{$sale->unique_transaction}}
					</td>
					<td>
						@if ($sale->type == 'P')
							Alta
						@else
							Recarga
						@endif
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
						{{ $sale->msisdn }}
					</td>
					<td>
						{{ number_format($sale->amount,2,'.',',') }}
					</td>
				</tr>
			@endforeach
			</tbody>
			<tfoot>
				<tr>
					<td colspan="8" align="right">
						<b>Monto total:</b>
					</td>
					<td>
						<b> {{ number_format($amount,2,'.',',') }} </b>
					</td>
					<td>
						<button type="button" class="btn btn-success btn-lg button" onclick="aprove()">Conciliar</button>
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
<script src="js/seller/comission/comission.js"></script>