$(document).ready(function () {
	$('#myTable').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
        }
    });
	$(".preloader").fadeOut();
	
});

function save () {
	var productsDetails = new Array();
    $('#myTable input[type=checkbox]').each(function () {
        if($(this).is(':checked')){
        	productsDetails.push($(this).val());
        }
    });

    $('#ids').val('['.concat(productsDetails).concat(']'));

    sav('#movewh_form', function (res) {
    	$('#details').html('');
    	getview('movewh/'.concat(getSelectObject('whini').getValue()), 'products');
    	alert(res);
    }, function (res) {
    });

    /*
    var frm = $('#movewh_form');
    frm.submit(function (e) {e.preventDefault();});
    */
}

$('button[type="submit"]').attr('disabled','disabled');
if ( ($('#whend').val()!= '')&&($('#whend').val() != $('#whini').val()) ) {
        $('button[type="submit"]').removeAttr('disabled');
    }
$('#whend').change ( function () {
    if ( $('#whend').val() != $('#whini').val() ) {
        $('button[type="submit"]').removeAttr('disabled');
    }else{
		$('button[type="submit"]').attr('disabled','disabled');
    }
});