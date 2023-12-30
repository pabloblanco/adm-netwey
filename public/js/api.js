function getview(view, containerId, hideLoader = true){
	$(".preloader").fadeIn();
	//containerId = 
	requestView (view, 'GET', function (res) {
		container=$('#'.concat((containerId == null || containerId == undefined || containerId == '') ? 'page-wrapper' : containerId));
		container.html(res.msg);
		if(hideLoader){
			$(".preloader").fadeOut();
		}
		container.show();
	}, function (res) {
		$(".preloader").fadeOut();
		alert(res.msg);
	})
}
function doRequest (req, protocol, params, onSuccess, onError) {
	$(".preloader").fadeIn();
	request(req, protocol, params, function (res) {
		$(".preloader").fadeOut();
		onSuccess(res);
	}, function (res) {
		$(".preloader").fadeOut();
		onError(res);
		console.log('error', res);
	})
}
function getsubview(view,params){
	$(".preloader").fadeIn();
	request('view/'.concat(view),'GET',params,function (res) {
		$(".preloader").fadeOut();
		$('#page-wrapper').html(res.msg);
	}, function (res) {
		$(".preloader").fadeOut();
		alert(res.msg);
	})
}