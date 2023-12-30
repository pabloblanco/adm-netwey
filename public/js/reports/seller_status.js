$(document).ready(function () {
	$(".preloader").fadeOut();
	var format = {autoclose: true, format: 'yyyy-mm-dd'};
	$('#date_ini').datepicker(format);
    $('#date_end').datepicker(format);
    $('#user_form').validate({
		rules: {
			seller: {
				required: true
			}
		},
		messages: {
			seller: "Debe seleccionar un vendedor"
		}
    });
    
});
function getReport () {
	var frm = $('#seller_status_form');
	frm.submit(function (e) {
		e.preventDefault();
	});
	if($('#seller').val() == null || $('#seller').val() == undefined || $('#seller').val() == '') {
		$('#error_seller').html('Debe seleccionar un vendedor');
	}else{
		$('#error_seller').html('');
		getViewFromForm (frm, 'report_container', function (res) {
			$('#report_container').html(res.msg);
		}, function (res) {
			$('#report_container').html('<br>error<br>'.concat(res));
		});
	}
}