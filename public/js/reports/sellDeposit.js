$(document).ready(function () {
	$(".preloader").fadeOut();
	$('#report_form').validate({
		rules: {
			n_tranfer:{
				number: true
			}
		},
		messages: {
			n_tranfer:{
				number: 'Ingrese solo numeros'
			}
		}
	});
});
function getReport () {
	if ($('#report_form').valid()) {
		$('#report_container').html('');
		getViewFromForm ($('#report_form'), 'report_container', function (res) {
			$('#report_container').html(res.msg);
		}, function (res) {
			$('#report_container').html('<br>error<br>'.concat(res));
		});
	} else {
		$('#report_form').submit(function (e) {
			e.preventDefault();
		})
	}
	
}