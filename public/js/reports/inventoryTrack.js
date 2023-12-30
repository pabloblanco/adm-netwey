function viewDatails(idd){

    var elem = $('div.details-control[data-id = '+idd+']');
    var table = $('#list-com').DataTable();
    var tr = elem.closest('tr');
    var row = table.row( tr );

    if ( row.child.isShown() ) {
        // This row is already open - close it
        row.child.hide();
        elem.removeClass('shown');
    }
    else {
        // Open this row
        if($(elem).data('fill')!='Y'){
            params={
                id:row.data().id
            };

            //elem=$(this);

            $('.preloader').show();
            request ('reports/get_dt_inventory_track_details', 'POST', params,
              function (res) {
                  if ( res.success ) {
                      elem.data('fill','Y');
                      row.child( res.msg );
                  } else {
                      row.child( 'Ocurrio un error en la consulta' );
                      console.log('error', res.errorMsg);
                  }
                  row.child.show();
                  elem.addClass('shown');
                  tr.next('tr').children('td').addClass('p-0');
                  $('.preloader').hide();
              },
              function (res) {
                  row.child( 'Ocurrio un error en la consulta' );
                  console.log('error', res.errorMsg);
                  row.child.show();
                  elem.addClass('shown');
                  $('.preloader').hide();
            });
            //row.child( row.data().msisdn );
        }
        else{
            row.child.show();
            elem.addClass('shown');
        }
    }
}

$(document).ready(function () {

    var configSelect = {
        valueField: 'msisdn',
        labelField: 'msisdn',
        searchField: 'msisdn',
        options: [],
        create: false,
        render: {
            option: function(item, escape) {
                return '<p>'+item.msisdn+'</p>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'view/reports/inventory_track/get-dns',
                type: 'POST',
                dataType: 'json',
                data: {
                    q: query
                },
                error: function() {
                    callback();
                },
                success: function(res){
                    if(res.success)
                        callback(res.dns);
                    else
                        callback();
                }
            });
        }
    };

    $('#msisdn_select').selectize(configSelect);

    $('#is_sell').selectize();

    var config = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        language: 'es',
        startDate: flimit,
        endDate: new Date()

    }


    $('#dateb').datepicker(config)
               .on('changeDate', function(selected){
                    var dt = $('#datee').val();
                    if(dt == ''){
                        $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
                        if(diff > maxdays){
                            $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
                        }
                    }

                    var diff2 = getDateDiff($('#datee').datepicker('getDate'), flimit);
                    if(diff2 > 0){
                        $('#datee').datepicker('update', flimit);
                    }
                    var maxDate = new Date(selected.date.valueOf());
                    $('#datee').datepicker('setStartDate', maxDate);

               });

    //config.endDate = new Date(new Date().setTime(new Date().getTime()- (24*60*60*1000)));
    config.endDate = new Date(new Date().setTime(new Date().getTime()));
    $('#datee').datepicker(config)
               .on('changeDate', function(selected){
                var dt = $('#dateb').val();
                    if(dt == ''){
                        $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
                        if(diff > maxdays){
                            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), - maxdays));
                        }
                    }
                    var diff2 = getDateDiff($('#dateb').datepicker('getDate'), flimit);
                    if(diff2 > 0){
                        $('#dateb').datepicker('update', flimit);
                    }
                    var maxDate = new Date(selected.date.valueOf());
                    $('#dateb').datepicker('setEndDate', maxDate);

               });


    $('#search').on('click', function(e){
        $('.preloader').show();

        if ($.fn.DataTable.isDataTable('#list-com')){
            $('#list-com').DataTable().destroy();
        }

        var table = $('#list-com').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: "reports/get_dt_inventory_track",
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                    d.dateb = $('#dateb').val();
                    d.datee = $('#datee').val();
                    d.msisdn_select = getSelectObject('msisdn_select').getValue();
                    d.is_sell = $('#is_sell').val();
                },

                type: "POST"
            },
            initComplete: function(settings, json){
                $(".preloader").fadeOut();
                $('#rep-sc').attr('hidden', null);
            },
            deferRender: true,
            ordering: false,
            columns: [
                //{data: null, orderable: false, className:'details-control', defaultContent: ''},
                {data: null, render: function(data,type,row,meta){
                    html = '';
                        html = html + '<div data-id="'+row.id+'" class="details-control" onclick="viewDatails('+row.id+')"></div>';
                    return html;
                }, searchable: false, orderable: false  },
                {data: 'id', searchable: false, orderable: false, visible:false},
                {data: 'msisdn', searchable: false, orderable: false},
                {data: 'sku', searchable: false, orderable: false},
                {data: 'article', searchable: false, orderable: false}
            ]
        });

         // Add event listener for opening and closing details
        // $('#list-com tbody').on('click', 'td.details-control', function () {
        //     var tr = $(this).closest('tr');
        //     var row = table.row( tr );

        //     if ( row.child.isShown() ) {
        //         // This row is already open - close it
        //         row.child.hide();
        //         tr.removeClass('shown');
        //     }
        //     else {
        //         // Open this row
        //         //row.child( format(row.data()) ).show();
        //         if($(this).data('fill')!='Y'){
        //             params={
        //                 id:row.data().id
        //             };

        //             elem=$(this);

        //             $('.preloader').show();
        //             request ('reports/get_dt_inventory_track_details', 'POST', params,
        //               function (res) {
        //                   if ( res.success ) {
        //                       elem.data('fill','Y');
        //                       row.child( res.msg );
        //                   } else {
        //                       row.child( 'Ocurrio un error en la consulta' );
        //                       console.log('error', res.errorMsg);
        //                   }
        //                   row.child.show();
        //                   tr.addClass('shown');
        //                   tr.next('tr').children('td').addClass('p-0');
        //                   $('.preloader').hide();
        //               },
        //               function (res) {
        //                   row.child( 'Ocurrio un error en la consulta' );
        //                   console.log('error', res.errorMsg);
        //                   row.child.show();
        //                   tr.addClass('shown');
        //                   $('.preloader').hide();
        //             });


        //             //row.child( row.data().msisdn );

        //         }
        //         else{
        //             row.child.show();
        //             tr.addClass('shown');
        //         }
        //     }
        // } );
    });

    $('#download').on('click', function(e){
        var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

        $(".preloader").fadeIn();

        $.ajax({
            type: "POST",
            url: "reports/download_dt_inventory_tracks",
            data: data,
            dataType: "text",
            success: function(response){
                $(".preloader").fadeOut();
                swal('Generando reporte','El reporte estara disponible en unos minutos.','success');
            },
            error: function(err){
                $(".preloader").fadeOut();
                swal('Error','No se pudo generar el reporte.','error');
            }
        });
    });
});
