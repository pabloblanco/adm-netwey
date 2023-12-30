function getReport () {
	$('#report_container').html('');
	getViewFromForm ($('#report_form'), 'report_container', function (res) {
		$('#report_container').html(res.msg);
	}, function (res) {
		$('#report_container').html('<br>error<br>'.concat(res));
	});
}

$(document).ready(function () {
	$('#org').on('change', function(e){
		var org = $('#org').val();

		if(org == ""){
			org = "ALL";
		}

		$('#warehouse').html('');

		$('#warehouse').append($('<option>', {
		    value: "",
		    text: 'Todos'
		}));

		$.ajax({
	        url: 'view/reports/get_warehouses/'+org,
	        type: 'GET',
	        dataType: 'json',
	        success: function (res) {
	        	if(res.length > 0){
	        		res.forEach(function(b){
	        			$('#warehouse').append($('<option>', {
						    value: b.id,
						    text: b.name
						}));
	        		});
	        	}
	        },
	        error: function (res) {
	            alert('Error no se cargaron las bodegas.')
	        }
	    });
	});
});