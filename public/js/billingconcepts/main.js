var fv;

function save () {
	
	$('input#nro_identification').val($.trim($('input#nro_identification').val()));
	$('input#description').val($.trim($('input#description').val()));
	$('input#product_key').val($.trim($('input#product_key').val()));
	$('input#unit_key').val($.trim($('input#unit_key').val()));
	$('input#unit').val($.trim($('input#unit').val()));

	let rules = {
		nro_identification: {
			required: true,
		    minlength: 3,
		    maxlength: 10
		},
		description: {
			required: true,
		    minlength: 3,
		    maxlength: 255
		},
		unit_key: {
			required: true,
		    minlength: 2,
		    maxlength: 10
		},
		unit: {
		    minlength: 0,
		    maxlength: 10
		},
		product_key: {
			required: true,
		    minlength: 3,
		    maxlength: 10
		},
		service_id: {
			required:true
		}
	};

	let messages = {
		nro_identification: "Por Favor ingrese ud Id MisKuentas válido",
		description: "Por Favor ingrese una Descripción válida",
		unit_key: "Por Favor ingrese una Unidad válida",
		unit: "Por Favor ingrese un Nombre de Unidad válido",
		product_key: "Por Favor ingrese una Clave de Producto válida",
		service_id: "Por Favor seleccione un id de servicio"
	}

	if(fv){
		fv.destroy();
	}

	fv = $('#billing_form').validate({
    	rules: rules,
    	messages: messages
    });

	if ($('#billing_form').valid()){
		sav('#billing_form', function (res) {
			if ( res.success ) {
				getview('billing/concepts');
				alert(res.msg);
				$('#myModal .close').trigger('click');
			} else {
				$(".preloader").fadeOut();
				alert(res.msg);
				console.log('error', res.errorMsg);
				$('#myModal .close').trigger('click');
			}
		},
		function (res) {
			alert(res.msg);
			console.log('error', res.errorMsg);
		});
	}else{
		$('#billing_form').submit(function (e) {
			e.preventDefault();
		});
	}
}

function update (object) {
	// console.log(object);
	// console.log(object.replace(/\'/g, '"'))
	obj = JSON.parse(object.replace(/\'/g, '"'));
	setModal(obj);
	$('#myModal').modal({backdrop: 'static', keyboard: false});
}

// function deleteData (id, name) {
// 	if (confirm('¿desea eliminar el servicio blim: "'+name+'"?')){
// 		request ('api/blimservices/'.concat(id), 'DELETE', null,
// 			function (res) {
// 				if ( res.success ) {
// 					getview('blim/services');
// 					alert(res.msg);
// 				} else {
// 					alert(res.msg);
// 					console.log('error', res.errorMsg);
// 				}
// 			},
// 			function (res) {
// 				alert(res.msg);
// 				console.log('error', res.errorMsg);
// 			});
// 	}
// }

function setModal(object) {
	if (object != null) {
		$('h4.modal-title').text('Editar datos: '.concat(object.id,' - ',object.description));
		$('#id').val(object.id);
		$('#nro_identification').val(object.nro_identification);
		$('#description').val(object.description);
		$('#product_key').val(object.product_key);
		$('#unit_key').val(object.unit_key);
		$('#unit').val(object.unit);
		$('#service_id').val(object.service_id);
		$('#pack_id').val(object.pack_id);
		if(object.shipping == 'No')
			$('#shipping').val('N');
		else
			$('#shipping').val('Y');

		if(object.is_financed == 'No')
			$('#is_financed').val('N');
		else
			$('#is_financed').val('Y');

		$('#billing_form').attr('action', 'api/billingconcepts/'.concat(object.id));
		$('#billing_form').attr('method', 'PUT');

	} else {
		$('h4.modal-title').text('Crear concepto de facturación');
		$('#id').val('');
		$('#name').val('');
		$('#description').val('');
		$('#price').val('');
		$('#sku').val('');
		$('#status').val('A');
		$('#billing_form').attr('action', 'api/billingconcepts/store');
		$('#billing_form').attr('method', 'POST');
	}
}

$('#myModal').on('hidden.bs.modal', function () {
    setModal(null);
});

$(document).ready(function () {
	
	// $(".preloader").fadeOut();
	// if ( ! $.fn.DataTable.isDataTable('#myTable') ) {
	// 	$('#myTable').DataTable({
	//         "language": {
 //            	"url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
 //        	},
	//         searching: false,
 //            processing: true,
 //            serverSide: true,
 //            ajax: 'api/billingconcepts/list-dt',
 //            order: [[ 1, "desc" ]],
	//         columns: [
	//             {data: 'id'},
	//             {data: 'nro_identification'},
	//             {data: 'description'},
	//             {data: 'unit_key'},
	//             {data: 'unit'},
	//             {data: 'service_id'},
	//             {data: 'pack_id'},
	//             {data: 'product_key'},
	//             {data: 'shipping'},
	//             {data: 'is_financed'}
	//         ]
	//     });
	// }



	drawTable = function(){
        if ($.fn.DataTable.isDataTable('#myTable')){
            $('#myTable').DataTable().destroy();
        }

        ordercol=0;
        columnss = [];

        if ( $('th#actionCol').length ) {
  			ordercol=1;
  			columnss = [
	        	{data: null, render: function(data,type,row,meta){
	        		html = '';
	        		if (row.action){
	        			jsoncad=JSON.stringify(row).replace(/"/g, '\\\'');
	        			html = html + '<button type="button" class="btn btn-info btn-md edit-bc" onclick="update(\''+jsoncad+'\')">Editar</button>';
	        		}
                    return html;
                }, searchable: false, orderable: false}
	        ];
  		}

		columnss.push({data: 'id'});
		columnss.push({data: 'nro_identification'});
		columnss.push({data: 'description'});
		columnss.push({data: 'unit_key'});
		columnss.push({data: 'unit'});
		columnss.push({data: 'service_id'});
		columnss.push({data: 'pack_id'});
		columnss.push({data: 'product_key'});
		columnss.push({data: 'shipping'});
		columnss.push({data: 'is_financed'});

        $('.preloader').show();

        $('#myTable').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'api/billingconcepts/list-dt',
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                },
                type: "POST"
            },
            initComplete: function(settings, json){
                // $('.delete-fi').bind('click', deleteFinan);
                // $('.edit-fi').bind('click', editFinan);
                $(".preloader").fadeOut();
            },
            order: [[ ordercol, "desc" ]],
            deferRender: true,
	        columns: columnss
        });
    }

    drawTable();

    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });
});