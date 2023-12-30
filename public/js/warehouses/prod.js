$(document).ready(function () {
	$(".preloader").fadeOut();
	$('#product').selectize({
    	onChange: function () {
	    	getview('movewh/'.concat(getSelectObject('whini').getValue())+'/'.concat(this.getValue()), 'details');
	    }
    });
});