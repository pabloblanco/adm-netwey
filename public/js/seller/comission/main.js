$(document).ready(function () {
    $(".preloader").fadeOut();
    $('#sellers').selectize({
    	onChange: function () {
    		if (this.getValue() != null && this.getValue() != undefined && this.getValue() != '') {
    			getview('seller/comission/'.concat(this.getValue()), 'sales_table_area');
    		} else {
    			$('#sales_table_area').html('');
    		}
	    }
    });
});