$(document).ready(function () {
	$(".preloader").fadeOut();
	var format = {autoclose: true, format: 'yyyy-mm-dd'};
	$('#date_ini').datepicker(format);
    $('#date_end').datepicker(format);
});
function getReport () {
    $('#report_container').html('');
    getViewFromForm ($('#report_form'), 'report_container', function (res) {
        $('#report_container').html(res.msg);
    }, function (res) {
        $('#report_container').html('<br>error<br>'.concat(res));
    });
}