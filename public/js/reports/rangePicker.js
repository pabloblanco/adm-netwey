$(document).ready(function() {
    $('form #dateStar').prop('readonly', true);
    $('form #dateEnd').prop('readonly', true);
    /*
     * Configuracion de fechas
     */
    var config = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        language: 'es',
        endDate: new Date()
    }
    /**
     * Fecha de incio
     */
    $('#dateStar').datepicker(config).on('changeDate', function(selected) {
        var dt = $('#dateEnd').val();
        if (dt == '') {
            $('#dateEnd').datepicker('setDate', $('#dateStar').datepicker('getDate'));
        }
        $('#dateEnd').datepicker('setStartDate', $('#dateStar').datepicker('getDate'));
    });
    config.endDate = new Date(new Date().setTime(new Date().getTime()));
    /**
     * Fecha de fin
     */
    $('#dateEnd').datepicker(config).on('changeDate', function(selected) {
        var dt = $('#dateStar').val();
        if (dt == '') {
            $('#dateStar').datepicker('update', sumDays($('#dateEnd').datepicker('getDate'), -30));
        }
    });
    /**
     * Fecha de fin al inicial la interfaz
     */
    $('#dateEnd').datepicker('setStartDate', $('#dateStar').datepicker('getDate'));
});