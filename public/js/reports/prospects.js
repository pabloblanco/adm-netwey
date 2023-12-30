getSellers = () => {
	$(".preloader").fadeIn();
    org=null;
    if($('#org').length){
    	$('#org').val().trim()
    }
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'reports/prospects/get-sellers',
        type: 'post',
        data: {org : org},
        success: function (res) {
            if(res){
                $("#seller [value!='']").remove();
                res.data.forEach(function(s){
                    $('#seller').append($('<option>', {
                                        value: s.email,
                                        text : s.name+' '+s.last_name
                                    }));
                });
            }
            $(".preloader").fadeOut();
        },
        error: function (res) {
            console.log(res);
            $(".preloader").fadeOut();
        }
    });
}

$(document).ready(function () {
	$(".preloader").fadeOut();
	var format = {autoclose: true, format: 'yyyy-mm-dd'};
	$('#date_ini').datepicker(format);
    $('#date_end').datepicker(format);

    if($('#org').length){
	    $('#org').on('change', function(e){
	    	getSellers();
	    });
	}

	getSellers();

    $('#date_ini').datepicker(format)
                .on('changeDate', function(selected){
                    var dt = $('#date_end').val();
                    if(dt == ''){
                        $('#date_end').datepicker('setDate', sumDays($('#date_ini').datepicker('getDate'), 90));
                    }else{
                        var diff = getDateDiff($('#date_ini').datepicker('getDate'), $('#date_end').datepicker('getDate'));
                        if(diff > 90)
                            $('#date_end').datepicker('setDate', sumDays($('#date_ini').datepicker('getDate'), 90));
                    }
                });

  $('#date_end').datepicker(format)
                .on('changeDate', function(selected){
                    var dt = $('#date_ini').val();
                    if(dt == ''){
                        $('#date_ini').datepicker('update', sumDays($('#date_end').datepicker('getDate'), -90));
                    }else{
                        var diff = getDateDiff($('#date_ini').datepicker('getDate'), selected.date);
                        if(diff > 90)
                            $('#date_ini').datepicker('update', sumDays($('#date_end').datepicker('getDate'), -90));
                    }
                });
});

function getReport () {
	$('#report_container').html('');
	getViewFromForm ($('#report_form'), 'report_container', function (res) {
		$('#report_container').html(res.msg);
	}, function (res) {
		$('#report_container').html('<br>error<br>'.concat(res));
	});
}