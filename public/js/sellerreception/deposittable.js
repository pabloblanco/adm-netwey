$(document).ready(function () {
	if (sales != null && sales != undefined && sales.length > 0)
	{
		sales.forEach(function (item, key){
			var id = '#report_deposit_form_'.concat(item);
			console.log(id, (objects[key]));
			$(id).validate((objects[key]));
		});
	}
});
function getDetail(id){
	var url = 'seller_reception/deposit/detail/'+id;
	getview(url,'body_detail');
    $(".preloader").fadeOut();
	$('#open_detail_btn').click();
}
function save(id){
	var id_form = '#report_deposit_form_'.concat(id);
	if ($(id_form).valid()) {
		$(id_form).submit(function (e) {
			e.preventDefault();
		})
		var params = new FormData();
		file = document.getElementById('image'+id).files[0];
	    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
	    params.append('image', file);
	    params.append('deposit', $('#deposit'+id).val());
	    params.append('bank', $('#bank'+id).val());
	    params.append('amount', $('#amount'+id).val());
	    $.ajax({
	        headers: {
	            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
	        },
	        contentType: false,
	        processData: false,
	        cache: false,
	        async: true,
	        url: $(id_form).attr('action'),
	        method: "POST",
	        data: params,
	        success: function (res) {
	        	setSelect('sellers', getSelectObject('sellers').getValue());
	            alert(res.msg);
	        },
	        error: function (res) {
	            console.log(res);
    			$(".preloader").fadeOut();
	        }
	    });
	} else {
		$(id_form).submit(function (e) {
			e.preventDefault();
		})
	}
	
}