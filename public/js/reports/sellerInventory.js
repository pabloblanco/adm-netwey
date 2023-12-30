var ban = true;

$(document).ready(function () {
	var format = {autoclose: true, format: 'yyyy-mm-dd'};
	$('#date_ini').datepicker(format);
	$('#date_end').datepicker(format);

	$('#org').selectize();
	
	var configSelect = {
		valueField: 'email',
		labelField: 'username',
		searchField: 'username',
		options: [],
		create: false,
		persist: false,
		render: {
				option: function(item, escape) {
						return '<p>'+item.name+' '+item.last_name+'</p>';
				}
		}
	};

	configSelect.load = function(query, callback){
		if (!query.length) return callback();
		$.ajax({
				headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: 'view/reports/filter/user',
				type: 'POST',
				dataType: 'json',
				data: {
					q: query,
					org: $('#org').val(),
					regional: $('#reg').val(),
					coord: $('#user').val(),
					seller: $('#seller').val(),
					call: 'regional'
				},
				error: function() {
						callback();
				},
				success: function(res){
						if(res.success)
								callback(res.users);
						else
								callback();
				}
		});
	}

	$('#reg').selectize(configSelect);

	configSelect.load = function(query, callback){
		if (!query.length) return callback();
		$.ajax({
				headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: 'view/reports/filter/user',
				type: 'POST',
				dataType: 'json',
				data: {
					q: query,
					org: $('#org').val(),
					regional: $('#reg').val(),
					coord: $('#user').val(),
					seller: $('#seller').val(),
					call: 'coord'
				},
				error: function() {
						callback();
				},
				success: function(res){
						if(res.success)
								callback(res.users);
						else
								callback();
				}
		});
	}
	
	$('#user').selectize(configSelect);

	configSelect.load = function(query, callback){
		if (!query.length) return callback();
		$.ajax({
				headers: {
						'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
				},
				url: 'view/reports/filter/user',
				type: 'POST',
				dataType: 'json',
				data: {
					q: query,
					org: $('#org').val(),
					regional: $('#reg').val(),
					coord: $('#user').val(),
					seller: $('#seller').val(),
					call: 'seller'
				},
				error: function() {
						callback();
				},
				success: function(res){
						if(res.success)
								callback(res.users);
						else
								callback();
				}
		});
	}
	$('#seller').selectize(configSelect);

	$(".preloader").fadeOut();
});

function getReport () {
	$('#report_container').html('');
	getViewFromForm ($('#report_form'), 'report_container', function (res) {
		$('#report_container').html(res.msg);
	}, function (res) {
		$('#report_container').html('<br>error<br>'.concat(res));
	});
}