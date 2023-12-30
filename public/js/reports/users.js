$(document).ready(function () {
	$(".preloader").fadeOut();
    var typeS = $('#type').selectize();
    var estatusS = $('#status').selectize();
    var nameS = $('#seller_name').selectize();
    var emailS = $('#seller_email').selectize();

    typeS = typeS[0].selectize;
    estatusS = estatusS[0].selectize;
    nameS = nameS[0].selectize;
    emailS = emailS[0].selectize;

    typeS.on('change', function(){
        var df = {
            type : typeS.getValue(),
            status: estatusS.getValue(),
            name: '',//nameS.getValue(),
            email: emailS.getValue(),
        };
        getFilterUsers(df,nameS,emailS);
    });

    /*estatusS.on('change', function(){
        var df = {
            type : typeS.getValue(),
            status: estatusS.getValue(),
            name: nameS.getValue(),
            email: emailS.getValue(),
        };
        getFilterUsers(df,nameS,emailS);
    });

    nameS.on('change', function(){
        var df = {
            type : typeS.getValue(),
            status: estatusS.getValue(),
            name: nameS.getValue(),
            email: emailS.getValue(),
        };
        getFilterUsers(df,nameS,emailS);
    });

    emailS.on('change', function(){
        var df = {
            type : typeS.getValue(),
            status: estatusS.getValue(),
            name: nameS.getValue(),
            email: emailS.getValue(),
        };
        getFilterUsers(df,nameS,emailS);
    });*/
});

function getFilterUsers(data,fname,femail){
    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/users/filter',
        type: 'post',
        data: data,
        success: function (res) {
            if(res){
                fname.clearOptions();
                femail.clearOptions();
                res.users.forEach(function(user){
                    fname.addOption({value:user.email, text:user.name+' '+user.last_name});
                    femail.addOption({value:user.email, text:user.email});
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

    var files = document.getElementById('msisdns_file');
    var params = new FormData();
    var label = 'type';
    if (getSelectObject(label).getValue() != undefined && getSelectObject(label).getValue() != null && getSelectObject(label).getValue() != '') {
		params.append(label, getSelectObject(label).getValue());
    }
    label = 'status';
    if (getSelectObject(label).getValue() != undefined && getSelectObject(label).getValue() != null && getSelectObject(label).getValue() != '') {
		params.append(label, getSelectObject(label).getValue());
    }
    label = 'seller_name';
    if (getSelectObject(label).getValue() != undefined && getSelectObject(label).getValue() != null && getSelectObject(label).getValue() != '') {
		params.append(label, getSelectObject(label).getValue());
    }
    label = 'seller_email';
    if (getSelectObject(label).getValue() != undefined && getSelectObject(label).getValue() != null && getSelectObject(label).getValue() != '') {
		params.append(label, getSelectObject(label).getValue());
    }

    $(".preloader").fadeIn();
    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'view/reports/users/detail',
        type: 'post',
        data: params,
        contentType: false,
        processData: false,
        cache: false,
        async: true,
        success: function (res) {
            $(".preloader").fadeOut();
			$('#report_container').html(res.msg);
        },
        error: function (res) {
            console.log(res);
            $(".preloader").fadeOut();
			$('#report_container').html('<br>error<br>'.concat(res));
        }
    });
}