$(document).ready(function () {
	if($.fn.DataTable.isDataTable('#dt-conc')){
        $('#dt-conc').DataTable().destroy();
    }

    $('#dt-conc').DataTable({
        language: {
        	sProcessing:     "Procesando...",
			sLengthMenu:     "Mostrar _MENU_ registros",
			sZeroRecords:    "No se encontraron resultados",
			sEmptyTable:     "Ningún dato disponible en esta tabla",
			sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
			sInfoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
			sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
			sInfoPostFix:    "",
			sSearch:         "Buscar:",
			sUrl:            "",
			sInfoThousands:  ",",
			sLoadingRecords: "Cargando...",
			oPaginate: {
				sFirst:    "Primero",
				sLast:     "Último",
				sNext:     "Siguiente",
				sPrevious: "Anterior"
			},
			oAria: {
				sSortAscending:  ": Activar para ordenar la columna de manera ascendente",
				sSortDescending: ": Activar para ordenar la columna de manera descendente"
			}
        },
        order: [[ 1, "desc" ]],
        processing: true,
        serverSide: true,
        deferRender: true,
        ajax: {
        	url: 'view/reports/concentrators/detail/detail-dt',
        	data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                var filters = $("#report_form").serializeArray();

                filters.forEach(function(e){
                	d[e.name] = e.value;
                });
            },
        	type: "POST"
        },
        columns: [
            { data: 'type', searchable: false},
            { data: 'pack', searchable: false},
            { data: 'article', searchable: false},
            { data: 'service', searchable: false},
        	{ data: 'unique_transaction', orderable: false},
            { data: 'concentrator', searchable: false, orderable: false},
            { data: 'date_reg', searchable: false},
            { data: 'msisdn', orderable: false},
            {data: 'sale_type', orderable: false},
            { data: 'amount', searchable: false},
            { data: 'conciliation', searchable: false},
        ]
    });

    $('#downloadR').on('click', function(e){
    	var value = '';
    	var data = $("#report_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content') + '&emails=' + value;

		$(".preloader").fadeIn();

		$.ajax({
		    type: "POST",
		    url: "view/reports/concentrators/detail/download",
		    data: data,
		    dataType: "text",
		    success: function(response){
		    	$(".preloader").fadeOut();
		    	swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
		    },
		    error: function(err){
		    	$(".preloader").fadeOut();
		    	swal('Error','No se pudo generar el reporte.','error');
		    }
		});
	});
});