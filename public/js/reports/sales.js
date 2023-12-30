$(document).ready(function () {
	$(".preloader").fadeOut();

	var format = {autoclose: true, format: 'yyyy-mm-dd'};
	$('#coverage-content').hide();
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

    $('#concentrator').selectize({
	    valueField: 'id',
	    labelField: 'name',
	    searchField: 'name'
	});
  $('#coverage_area').selectize({
	    valueField: 'id',
	    labelField: 'name',
	    searchField: 'name'
	});
	$('#type').selectize();
	$('#type_line').selectize();

    ajax1 = function(query, callback) {
              if (!query.length) return callback();
              $.ajax({
                  headers: {
                      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                  },
                  url: 'view/reports/get_filter_users_sellers',
                  type: 'POST',
                  dataType: 'json',
                  cache: false,
                  data: {
                      name: query,
                      org: '',
                      type: 'coordinador'
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

  ajax2 = function(query, callback) {
	  if (!query.length) return callback();
	  $.ajax({
	      headers: {
	          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	      },
	      url: 'view/reports/get_filter_users_sellers',
	      type: 'POST',
	      dataType: 'json',
	      data: {
	          name: query,
	          org: '',
	          coord: $('#coord').val(),
	          type: 'vendor'
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

  var configSelect = {
    valueField: 'email',
    labelField: 'username',
    searchField: 'username',
    options: [],
    create: false,
    persist: false,
    render:{
      option: function(item, escape) {
          return '<p>'+escape(item.name.toLocaleUpperCase())+' '+escape(item.last_name.toLocaleUpperCase())+'</p>';
      }
    }
  };

  configSelect.load = ajax1;
  $('#coord').selectize(configSelect);

  configSelect.load = ajax2;
  $('#seller').selectize(configSelect);

  $('#coord').on('change', function(e){
    var sel = $('#seller')[0].selectize;

    if(sel){
      sel.clearOptions();
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

$('#type_line').on('change', function(){
	if($(this).val() == 'F'){
		$('#coverage_area')[0].selectize.setValue('');
		$('#coverage-content').show();
	}else{
		$('#coverage-content').hide();
	}
});