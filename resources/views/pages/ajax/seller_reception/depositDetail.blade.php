@if (!empty($sales))
	<div class="table-responsive">
		<table id="myTable" class="table table-striped">
			<thead>
		            <tr>
		              <th>Transacción única</th>
		              <th>Fecha de la Transacción</th>
		              <th>Plan</th>
		              <th>Producto</th>
		              <th>Servicio</th>
		              <th>Cliente</th>
		              <th>Teléfono Netwey</th>
		              <th>Monto recibido</th>
		              <th>Monto pagado</th>
		            </tr>
		          </thead>
		          <tbody>
		            @foreach ($sales as $item)
		              <tr>
						<td>{{$item->sale->unique_transaction}}</td>
						<td>{{$item->sale->date_reg}}</td>
						<td>
							@if(!empty($item->sale->pack))
								{{$item->sale->pack}}
							@else
								N/A
							@endif
						</td>
						<td>
							@if(!empty($item->sale->article))
								{{$item->sale->article}}
							@else
								N/A
							@endif
							</td>
						<td>{{$item->sale->service}}</td>
						<td>{{$item->sale->client_name}} {{$item->sale->client_lname}}</td>
						<td>{{$item->sale->msisdn}}</td>
						<td>{{number_format($item->amount_text,2,'.',',')}}</td>
						<td>{{number_format($item->sale->amount,2,'.',',')}}</td>
		              </tr>
		            @endforeach
		          </tbody>
		</table>
	</div>
	<script type="text/javascript">
		$(document).ready(function () {
			$('#myTable').DataTable({
		        "language": {
		            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
		        }
		    });
		});
	</script>
@else
	<h3>No hay registros disponibles</h3>
@endif

