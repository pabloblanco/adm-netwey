function request (api, protocol, params, successCB, errorCB) {
	$.ajax({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		},
		async: true,
		url: api,
		method: protocol,
		data: params,
		success: function (res) {
			successCB(res);
		},
		error: function (res) {
			errorCB(res);
		}
	});
}
function requestView (view, protocol, successCB, errorCB) {
	$.ajax({
		async: true,
		url: 'view/'.concat(view),
		method: protocol,
		data: null,
		success: function (res) {
			successCB(res);
		},
		error: function (res) {		
			errorCB(res);
		}
	});
}