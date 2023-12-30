<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				@if (count($report) > 0)
					<header>Reporte de Bodegas</header>
				@else
					<header>No hay registros para estos parámetros</header>
				@endif
			</h3>
		</div>
	</div>
	@if (count($report) > 0)
		<div class="row">
			<div class="col-md-12">

			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12">
				<button class="btn btn-success" onclick="downloadcsv();">Exportar en CSV</button>
	       		<a id="download"></a>
			</div>
		</div>
		<br>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="myTable" class="table table-striped">
						<thead>
							<tr>
								<th>Acci&oacute;n</th>
								<th>Id Bodega</th>
								<th>Nombre Bodega</th>
								<th>Id Producto</th>
								<th>Titulo Producto</th>
								<th>Tipo</th>
								<th>Existencia</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($report as $item)
								<tr>
									<td class="row">
										@if (count($item->inv) > 0)
											<button type="button" class="btn btn-warning btn-md button col-md-12" onclick="prepareModal('{{ json_encode($item->inv) }}')">Ver inventario</button>
										@else
											<button type="button" class="btn btn-warning btn-md button col-md-12" disabled>Ver inventario</button>
										@endif
									</td>
									<td>{{$item->wh_id}}</td>
									<td>{{$item->wh_name}}</td>
									<td>{{$item->pro_id}}</td>
									<td>{{$item->pro_name}}</td>
									<td>
										@switch($item->artic_type)
										    @case('T') Telefonía @break
										    @case('F') Fibra @break
										    @case('M') MIFI @break
										    @default Internet Hogar
										@endswitch

									<td>
										{{count($item->inv)}}

									</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	@endif
</div>
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#myModal">&times;</button>
        <h4 id="modal-title" class="modal-title">Inventario existente</h4>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="myTableDetail" class="table table-striped">
						<thead>
							<tr>
								<th>MSISDN</th>
								<th>IMEI / MAC</th>
								<th>ICCID</th>
								<th>Fecha Registro</th>
							</tr>
						</thead>
						<tbody id="inventory_detail">
						</tbody>
					</table>
				</div>
			</div>
		</div>
      </div>
    </div>
  </div>
</div>
@if (count($report) > 0)
<div hidden>
	<table id="reportTable">
		<caption>Bodegas</caption>
		<thead>
			<tr>
				<th>Id</th>
				<th>Id Bodega</th>
				<th>Nombre Bodega</th>
				<th>Id Producto</th>
				<th>Titulo Producto</th>
				<th>MSISDN</th>
				<th>IMEI / MAC</th>
				<th>ICCID</th>
				<th>Fecha Registro</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($report as $item)
				@foreach ($item->inv as $inv)
					<tr>
						<td>{{$inv->id}}</td>
						<td>{{$item->wh_id}}</td>
						<td>{{$item->wh_name}}</td>
						<td>{{$item->pro_id}}</td>
						<td>{{$item->pro_name}}</td>
						<td>{{$inv->msisdn}}</td>
						<td>{{$inv->imei}}</td>
						<td>{{$inv->iccid}}</td>
						<td>{{$inv->date_reg}}</td>
					</tr>
				@endforeach
			@endforeach
		</tbody>
	</table>
</div>
@endif
<script src="js/reports/warehousesDetail.js?v=2.0"></script>
<script src="js/common-modals.js"></script>


