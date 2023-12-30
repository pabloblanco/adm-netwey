<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				<header>Reporte de concentradores</header>
			</h3>
		</div>
	</div>
	{{--@if (count($report) > 0)--}}
		<div class="row">
			<div class="col-md-12">
				<button class="btn btn-success" id="downloadR">Exportar en Excel</button>
			</div>
		</div>
		<br><br>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="dt-conc" class="table table-striped">
						<thead>
							<tr>
								<th>Tipo de venta</th>
								<th>Plan</th>
								<th>Producto</th>
								<th>Servicio</th>
								<th>Transacción única</th>
								<th>Concentrador</th>
								<th>
									Fecha </br> 
									<span style="font-size: 10px;display: block;width: 85px;">(YYYY-mm-dd)</span>
								</th>
								<th>MSISDN</th>
								<th>Tipo linea</th>
								<th>Monto pagado</th>
								<th>Conciliada</th>
							</tr>
						</thead>
						{{--<tbody>
							@foreach ($report as $item)
								<tr>
									<td>
										@if ($item->type == 'P')
											Alta
										@else
											Recarga
										@endif
									</td>
									<td>
										@if ($item->type == 'P')
											{{$item->pack}}
										@else
											N/A
										@endif
									</td>
									<td>
										@if ($item->type == 'P')
											{{$item->article}}
										@else
											N/A
										@endif
									</td>
									<td>{{$item->service}}</td>
									<td>{{$item->unique_transaction}}</td>
									<td>
										@if (isset($item->concentrator))
											{{$item->concentrator}}
										@else
											N/A
										@endif
									</td>
									<td>{{$item->date_reg}}</td>
									<td>{{$item->msisdn}}</td>
									<td>{{number_format($item->amount,2,'.',',')}}</td>
									<td>
										@if ($item->conciliation == 'Y')
											Si
										@else
											No
										@endif
									</td>
								</tr>
							@endforeach
						</tbody>--}}
					</table>
				</div>
			</div>
		</div>
	{{--@endif--}}
</div>
{{--@if (count($report) > 0)
	<table hidden id="reportTable" class="table table-striped">
		<caption>Concentradores</caption>
		<thead>
			<tr>
				<th>Tipo de venta</th>
				<th>Plan</th>
				<th>Producto</th>
				<th>Servicio</th>
				<th>Transacción única</th>
				<th>Concentrador</th>
				<th>Fecha de la Transacción</th>
				<th>MSISDN</th>
				<th>Monto pagado</th>
				<th>Conciliada</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($report as $item)
				<tr>
					<td>
						@if ($item->type == 'P')
							Alta
						@else
							Recarga
						@endif
					</td>
					<td>
						@if ($item->type == 'P')
							{{$item->pack}}
						@else
							N/A
						@endif
					</td>
					<td>
						@if ($item->type == 'P')
							{{$item->article}}
						@else
							N/A
						@endif
					</td>
					<td>{{$item->service}}</td>
					<td>{{$item->unique_transaction}}</td>
					<td>
						@if (isset($item->concentrator))
							{{$item->concentrator}}
						@else
							N/A
						@endif
					</td>
					<td>{{$item->date_reg}}</td>
					<td>{{$item->msisdn}}</td>
					<td>{{number_format($item->amount,2,'.',',')}}</td>
					<td>
						@if ($item->conciliation == 'Y')
							Si
						@else
							No
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif--}}
<script src="js/reports/concentratorsDetail.js?v=2.0"></script>