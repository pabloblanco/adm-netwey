$(document).ready(function () {
	$(".preloader").fadeOut();
	$('#whend').selectize();
	$('#whendfile').selectize();
	$('#whinifile').selectize();
	$('#whini').selectize({
    	onChange: function () {
    		$('#details').html('');
	    	getview('movewh/'.concat(this.getValue()), 'products');
	    }
    });
    $( "#file_form" ).validate({
        ignore: ':hidden:not([class~=selectized]),:hidden > .selectized, .selectize-control .selectize-input input',
        rules: {
            csv: {
                required: true,
                extension: "csv"
            },
            whendfile: {
                required: true,
                notEqualTo: "#whinifile"
            },
            whinifile: {
                required: true
            }
        },
        messages: {
            csv: {
                required: "Ingrese un archivo con formato .CSV",
                extension: "El archivo no cumple con el formato CSV"
            },
            whendfile: {
                required: 'Seleccione una bodega',
                notEqualTo:'Seleccione una bodega distinta a la bodega de inicio'
            },
            whinifile: 'Seleccione una bodega'
        }
    }); 
});

$("#fileup").click(function () {
    $('#file_form').submit(function (e) {e.preventDefault();})
    if ($('#file_form').valid()){
        savefile();
    }
});

function savefile(){
	var params = new FormData();
	file = document.getElementById('csv').files[0];
    params.append('csv', file);
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    params.append('whendfile', getSelectObject('whendfile').getValue());
    //params.append('whinifile', getSelectObject('whinifile').getValue());
    if($('#remove-inv-file').is(':checked')){
        params.append('removeInv', 'Y');
    }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        contentType: false,
        processData: false,
        cache: false,
        async: true,
        url: 'api/movewh/move-csv',
        method: 'POST',
        data: params,
        success: function (res) {
            alert(res);
        },
        error: function (res) {
            console.log(res);
        }
    });
}