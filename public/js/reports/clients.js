$(document).ready(function () {
	$(".preloader").fadeOut();

	var format = {autoclose: true, format: 'yyyy-mm-dd'};
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


    $('#msisdn_select_container').hide();

    var configSelect = {
        valueField: 'msisdn',
        labelField: 'msisdn',
        searchField: 'msisdn',
        options: [],
        create: false,
        render: {
            option: function(item, escape) {
                return '<p>'+item.msisdn+'</p>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'view/reports/clients/get-dns',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: query
                },
                error: function() {
                    callback();
                },
                success: function(res){
                    if(res.success)
                        callback(res.clients);
                    else
                        callback();
                }
            });
        }
    };

    $('#msisdn_select').selectize(configSelect);

    $('#service').selectize();
    $('#type_line').selectize();

    $('#client_manual_check').click(function(){
        if($('#client_manual_check').is(':checked')){
            $('#msisdn_file_container').hide();
            $('#msisdn_select_container').show();
        } else {
            $('#msisdn_file_container').show();
            $('#msisdn_select_container').hide();
        }
    });
});
function getReport () {

	$('#report_container').html('');

    var files = document.getElementById('msisdns_file');
    var params = new FormData();
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if ($('#client_manual_check').is(':checked')) {
        params.append('msisdn_select', getSelectObject('msisdn_select').getValue());
    } else {
    	if (files != undefined && files != null) {
        	params.append('msisdn_file', files.files[0]);
    	}
    }
    params.append('date_ini', $('#date_ini').val());
    params.append('date_end', $('#date_end').val());
    params.append('service', $('#service').val());
    params.append('type_line', $('#type_line').val());
    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/clients/detail',
        type: 'post',
        data: params,
        contentType: false,
        processData: false,
        cache: false,
        async: true,
        success: function (res) {
			$('#report_container').html(res.msg);
            $(".preloader").fadeOut();
        },
        error: function (res) {
            console.log(res);
            alert('Hubo un error');
			$('#report_container').html('<br>error<br>'.concat(res));
            $(".preloader").fadeOut();
        }
    });
}