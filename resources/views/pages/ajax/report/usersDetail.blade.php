<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				@if (count($report) > 0)
					<header>Reporte de estado de vendedores</header>
				@else
					<header>No hay registros para estos parámetros</header>
				@endif
			</h3>
		</div>
	</div>
	@if (count($report) > 0)
		<div class="row">
			<div class="col-md-12">
				<button class="btn btn-success" onclick="downloadcsv();">Exportar en CSV</button>
	       		<a id="download"></a>
			</div>
		</div>
		<br><br>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="myTable" class="table table-striped">
						<thead>
							<tr>
								<th>Acciones</th>
								<th>Supervisor</th>
								<th>Nombre</th>
								<th>Apellido</th>
								<th>Teléfono</th>
								<th>Email</th>
								<th>Dirección</th>
								<th>Tipo</th>
								<th>Estado</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($report as $item)
								<tr>
									<td>
										<button type="button" class="btn btn-info btn-md button" onclick="detail('{{$item->email}}')">Ver</button>
									</td>
									<td>{{$item->parent}}</td>
									<td>{{$item->name}}</td>
									<td>{{$item->last_name}}</td>
									<td>{{$item->phone}}</td>
									<td>{{$item->email}}</td>
									<td>{{$item->address}}</td>
									<td>{{$item->type}}</td>
									<td>
										@if ($item->status == 'A')
											Activo
										@endif
										@if ($item->status == 'I')
											Inactivo
										@endif
										@if ($item->status == 'T')
											Eliminado
										@endif
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
@if (count($report) > 0)
	<table hidden id="reportTable" class="table table-striped">
		<caption>Estado de vendedores</caption>
		<thead>
			<tr>
				<th>Supervisor</th>
				<th>Nombre</th>
				<th>Apellido</th>
				<th>Teléfono</th>
				<th>Email</th>
				<th>Dirección</th>
				<th>Tipo</th>
				<th>Estado</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($report as $item)
				<tr>
					<td>{{$item->parent}}</td>
					<td>{{$item->name}}</td>
					<td>{{$item->last_name}}</td>
					<td>{{$item->phone}}</td>
					<td>{{$item->email}}</td>
					<td>{{$item->address}}</td>
					<td>{{$item->type}}</td>
					<td>
						@if ($item->status == 'A')
							Activo
						@endif
						@if ($item->status == 'I')
							Inactivo
						@endif
						@if ($item->status == 'T')
							Eliminado
						@endif
					</td>
				</tr>
			@endforeach
		</tbody>
	</table>
@endif
<!--modal de detalle-->
{{-- <button hidden type="button" id="open_detail_btn" data-toggle="modal" data-target="#detail"></button> --}}
<div class="modal modalAnimate" id="detail" role="dialog">
  <div class="modal-dialog" id="modal01">
    <!-- Modal content-->
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#detail">&times;</button>
        <h4 class="modal-title">Detalles</h4>
      </div>
      <div class="modal-body" id="modal_report_container">
      </div>
    </div>
  </div>
</div>
<script src="js/reports/usersDetail.js"></script>
<script src="js/common-modals.js"></script>