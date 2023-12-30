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

  $('#org').on('change', function(e){
    var coor = $('#coord')[0].selectize;

    if(coor){
        coor.clearOptions();
    }

    var sel = $('#seller')[0].selectize;
    
    if(sel){
        sel.clearOptions();
    }
  });

  $('#org').selectize({
    valueField: 'id',
    labelField: 'business_name',
    searchField: 'business_name'
  });

  $('#service').selectize({
    valueField: 'id',
    labelField: 'description',
    searchField: 'description'
  });

  $('#coverage_area').selectize({
    valueField: 'id',
    labelField: 'name',
    searchField: 'name'
  });

  $('#product').selectize({
    valueField: 'id',
    labelField: 'title',
    searchField: 'title'
  });

  $('#type_buy').selectize();
  $('#conciliation').selectize();
  $('#type_line').selectize();

  if($('#serviceability').length){
    $('#serviceability').selectize();
  }

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
                      org: $('#org').val(),
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
                      org: $('#org').val(),
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
                  
    /*$('#supervisor').on('change', function () {
        if ($(this).val() != '') {
            $("#seller option[value=only_supervisor]").show();
        } else {
            $("#seller option[value=only_supervisor]").hide();
        }
    });

    if($('#org').length){
	    $('#org').on('change', function(e){
	    	var df = {
	            org : $('#org').val(),
	            coord: $('#supervisor').val()
	        };
	        getFilterUsers(df);
	    });
	}

    $('#supervisor').on('change', function(e){
    	var df = {
            org : $('#org').val(),
            coord: $('#supervisor').val()
        };
        getFilterUsers(df);
    });*/
});

function getFilterUsers(data){
    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/ur/users/filter',
        type: 'post',
        data: data,
        success: function (res) {
            if(res){
                $("#seller [value!='']").remove();
                $("#supervisor [value!='']").remove();
                res.cs.forEach(function(c){
                	var optVal = { 
                                    value: c.email,
                                    text : c.name+' '+c.last_name
                                 }
                	if(data.coord == c.email) optVal.selected = true;
                    $('#supervisor').append($('<option>', optVal));
                });
                res.ss.forEach(function(s){
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

function getReport () {
	$('#report_container').html('');
    
	getViewFromForm ($('#uporrecharge_form'), 'report_container', function (res) {
		$('#report_container').html(res.msg);
	}, function (res) {
		$('#report_container').html('<br>error<br>'.concat(res));
	});
}

$('#type_line').on('change', function() {

  $('#coverage_area').val('');
  $('#coverage_area')[0].selectize.setValue('');

  if ($(this).val() == 'F')
    $('#coverage-content').show();
  else
    $('#coverage-content').hide();

});