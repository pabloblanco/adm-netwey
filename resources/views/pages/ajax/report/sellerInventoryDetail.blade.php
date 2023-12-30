
<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				{{--@if (count($report) > 0)--}}
					<header>Reporte de Inventario por vendedores/coordinadores</header>
				{{--@else
					<header>No hay registros para estos parámetros</header>
				@endif--}}
			</h3>
		</div>
	</div>
	{{--@if (count($report) > 0)--}}
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
								<th>Usuario</th>
								<th>Email</th>
								<th>Supervisor</th>
								<th>Id Producto</th>
								<th>Producto</th>
								<th>N° Piezas</th>
								<th></th>
							</tr>
						</thead>
					</table>
				</div>
			</div>
		</div>
	{{--@endif--}}
</div>
{{-- <button type="button" id="open_modal_btn" class="btn btn-info btn-lg" data-toggle="modal" data-target="#myModal" hidden>Agregar</button> --}}
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#myModal">&times;</button>
        <h4 id="modal-title" class="modal-title">Inventario asignado</h4>
      </div>
      <div class="modal-body">
		<div class="row">
			<div class="col-md-12">
				<div class="table-responsive">
					<table id="myTableDetail" class="table table-striped">
						<thead>
							<tr>
								<th>Producto</th>
								<th>MSISDN</th>
								<th>IMEI / MAC</th>
								<th>ICCID</th>
								<th>Fecha Creaci&oacute;n</th>
								<th>Fecha primera asignaci&oacute;n</th>
								<th>Fecha ultima asignaci&oacute;n</th>
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

<script>
	var user = '0';//$('#seller').val() == '' ? $('#user').val() : $('#seller').val();
	var product = $('#product').val();
	var org = null;

	if($('#org').length > 0){
		org = $('#org').val();
	}

	if($('#seller').val() != ''){
		user = $('#seller').val();
	}else{
		if($('#user').val() != ''){
			user = $('#user').val();
		}else{
			if($('#reg').val() != ''){
				user = $('#reg').val();
			}
		}
	}

	if(product == undefined || product == null || product == ''){
		product = '0';
	}
	/*if(user == undefined || user == null || user == ''){
		user = '0';
	}*/
	if(org == undefined || org == null || org == ''){
		org = '0';
	}
</script>
<script src="js/reports/sellerInventoryDetail.js"></script>
<script src="js/common-modals.js"></script>