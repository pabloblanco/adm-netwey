$(document).ready(function () {
	$(".preloader").fadeOut();

	$('#kpi_dismissal_form').validate({
		errorClass: "myErrorClass",
		rules: {
			year_list:{
				requred: true,
			},
			month_list:{
				requred: true,
			}
		},
		messages: {
			year_list:{
				requred: 'Seleccione un Año'
			},
			month_list:{
				requred: 'Seleccione un Mes'
			}
		}
	});

	$('#year_list').selectize();
	$('#month_list').selectize({
		valueField: 'id',
		labelField: 'name',
		searchField: 'name'
	});

	var meses = ["Enero", "Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"]

	$('#year_list').on('change',function(){
		if($(this).val()!= ""){
			$(".preloader").fadeIn();
			data = {
				year:$(this).val()
			}
			request (
				"view/low/getMonthsAvailables",
				"POST",
				data,
				function(res){
					$(".preloader").fadeOut();
					let monthList = $('#month_list')[0].selectize;
					monthList.clearOptions();

					res.months.forEach(function(ele){

						//console.log(ele.month);

						monthList.addOption({
							id: ele.month,
							name: meses[ele.month-1]
						});

						monthList.addItem(ele.month);
					});
					monthList.setValue("");
					monthList.enable();
				},
				function(res){
					$(".preloader").fadeOut();

				}
			);
		}
		else{
			let monthList = $('#month_list')[0].selectize;
			monthList.clearOptions();
			monthList.disable();
		}
	});
    
});
function getReport () {
	var frm = $('#kpi_dismissal_form');
	frm.submit(function (e) {
		e.preventDefault();
	});

	if(frm.valid()){
		$('.preloader').show();

        if ($.fn.DataTable.isDataTable('#list-com')){
            $('#list-com').DataTable().destroy();
        }

        var table = $('#list-com').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "reports/kpi-dismissal/get-dt",
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.year = $('#year_list').val();
                    d.month = $('#month_list').val();
                },

                type: "POST"
            },
            initComplete: function(settings, json){
                $(".preloader").fadeOut();
                $('#rep-sc').attr('hidden', null);
                $('#title-rep').text('KPI Articulos Perdidos de '+$('#month_list').text()+' del '+$('#year_list').val());
            },
            deferRender: true,
            ordering: false,
            columns: [
                {   data: 'id', searchable: false, orderable: false, visible:false  },
                {   data: 'periodo', searchable: false, orderable: false, visible:false  },
                {   data: 'regional_email', searchable: false, orderable: false },
                {   data: 'coordinator_email', searchable: false, orderable: false },
                {   data: 'old_articles', searchable: false, orderable: false },
                {   data: 'decrease_articles', searchable: false, orderable: false },
                {   data: 'assigned_articles', searchable: false, orderable: false },
                {   data: 'kpi_result', searchable: false, orderable: false },
                {   data: 'lost_articles_cost', searchable: false, orderable: false },
                {   data: 'total_perc_discount', searchable: false, orderable: false },
                {   data: 'total_amount_discount', searchable: false, orderable: false },
                {   data: 'regional_perc_discount', searchable: false, orderable: false },
                {   data: 'regional_amount_discount', searchable: false, orderable: false },
                {   data: 'coordinator_perc_discount', searchable: false, orderable: false },
                {   data: 'coordinator_amount_discount', searchable: false, orderable: false }
            ]
        });

        $('#download').on('click', function(e){
	        var data = $("#kpi_dismissal_form").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

	        $(".preloader").fadeIn();

	        $.ajax({
	            type: "POST",
	            url: "reports/kpi-dismissal/download-dt",
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
    }
}
