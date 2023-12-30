<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				@if (count($report) > 0)
					<header>Reporte de Asignación de saldo</header>
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
		<br>
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="myTable" class="table table-striped">
						<thead>
							<tr>
								<th>Acci&oacute;n</th>
								<th>Id</th>
								<th>Usuario</th>
								<th>Banco</th>
								<th>Monto</th>
							</tr>
						</thead>
						<tbody>
							@foreach ($report as $item)
								<tr>
									<td>
										<button class="btn btn-danger" onclick="detail({{json_encode($item)}});">Detalles</button>
									</td>
									<td>{{$item->id}}</td>
									<td>{{$item->users_email}}</td>
									<td>{{$item->name}} n°:{{$item->numAcount}} </td>
									<td>{{$item->amount}}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>
	@endif
</div>
{{-- <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" hidden>Agregar</button> --}}
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#myModal">&times;</button>
        <h4 id="modal-title" class="modal-title">Detalles</h4>
      </div>
      <div class="modal-body">
		<div class="row">
          <div class="col-md-12">
          	<h4 class="modal-title"></h4>
            <hr>
            <div class="card card-outline-primary text-center text-dark">
              	<div class="card-block">
              		<div class="row">
		              <div class="col-md-3">
		                <label class="control-label">Id: <strong><span id="id"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Usuario: <strong><span id="user"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Banco: <strong><span id="banco"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Monto: <strong><span id="amount"></span></strong></label>
		              </div>
		            </div>
		            <div class="row">
		              <div class="col-md-3">
		                <label class="control-label">Descripción: <strong><span id="description"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Fecha de deposito: <strong><span id="dateDep"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Fecha de asignación: <strong><span id="dateAssig"></span></strong></label>
		              </div>
		              <div class="col-md-3">
		                <label class="control-label">Estatus del deposito: <strong><span id="status"></span></strong></label>
		              </div>
		            </div>
              	</div>
          	</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div hidden>
	<table id="reportTable">
		<caption>Asignación de saldo</caption>
		<thead>
			<tr>
				<th>Id</th>
				<th>Usuario</th>
				<th>Banco</th>
				<th>Monto</th>
				<th>Descripción</th>
				<th>Fecha de deposito</th>
				<th>Fecha de asignación</th>
				<th>Estatus del deposito</th>
			</tr>
		</thead>
		<tbody>
			@foreach ($report as $item)
				<tr>
					<td>{{$item->id}}</td>
					<td>{{$item->users_email}}</td>
					<td>{{$item->name}} n°:{{$item->numAcount}} </td>
					<td>{{$item->amount}}</td>
					<td>{{$item->description}}</td>
					<td>{{$item->date_deposit}}</td>
					<td>{{$item->date_asigned}}</td>
					<td>{{$item->deposit_status}}</td>
				</tr>
			@endforeach
		</tbody>
	</table>
</div>
<script src="js/reports/balanceDetail.js"></script>
<script src="js/common-modals.js"></script>