<div class="white-box">
	<div class="row">
		<div class="col-md-12">
			<h3>
				<header>No hay registros para estos parámetros</header>
			</h3>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
            <button class="btn btn-success" id="downloadR">Exportar en Excel</button>
		</div>
	</div>
	<br>
	<div class="row">
		<div class="col-md-12">
			<div class="table-responsive">
				<table id="dt-prospect" class="table table-striped">
					<thead>
						<tr>
							<th>Fecha de registro</th>
							<th>Nombre</th>
							<th>Email</th>
							<th>Teléfono</th>
							<th>Dirección</th>
							<th>Nota</th>
							<th>Pr&oacute;ximo contacto</th>
							<th>Persona que le registro</th>
							<th>Coordinador</th>
							<th>Organización</th>
							<th>Campaña</th>
						</tr>
					</thead>
					{{--<tbody>
						@foreach ($report as $item)
							<tr>
								<td>{{$item->date_reg}}</td>
								<td>{{$item->name}} {{$item->last_name}}</td>
								<td>{{$item->email}}</td>
								<td>{{$item->phone_home}}</td>
								<td>{{$item->address}}</td>
								<td>{{empty($item->note)? 'n/A' : $item->note}}</td>
								<td>{{empty($item->contact_date)? 'n/A' : $item->contact_date}}</td>
								<td>@if(!empty($item->seller_name)){{$item->seller_name}} {{$item->seller_last_name}} @else N/A @endif</td>
								<td>@if(!empty($item->name_coord)){{$item->name_coord}} {{$item->last_name_coord}}@else N/A @endif</td>
								<td>{{empty($item->business_name)? 'N/A' : $item->business_name}}</td>
							</tr>
						@endforeach
					</tbody>--}}
				</table>
			</div>
		</div>
	</div>
</div>

<script src="js/reports/prospectsDetail.js"></script>