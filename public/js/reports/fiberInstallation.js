function viewDatails(id) {
   var elem = $('div.details-control[data-id = ' + id + ']');
   var table = $('#list-com').DataTable();
   var tr = elem.closest('tr');
   var row = table.row(tr);
   if (row.child.isShown()) {
      // This row is already open - close it
      row.child.hide();
      elem.removeClass('shown');
   } else {
      // Open this row
      if ($(elem).data('fill') != 'Y') {
         params = {
            group_install: row.data().group_install
         };
         //elem=$(this);
         $('.preloader').show();
         request('reports/fiber_installations/get-dt-details', 'POST', params, function(res) {
            if (res.success) {
               elem.data('fill', 'Y');
               row.child(res.msg);
            } else {
               row.child('Ocurrio un error en la consulta');
               console.log('error', res.errorMsg);
            }
            row.child.show();
            elem.addClass('shown');
            tr.next('tr').children('td').addClass('p-0');
            $('.preloader').hide();
         }, function(res) {
            row.child('Ocurrio un error en la consulta');
            console.log('error', res.errorMsg);
            row.child.show();
            elem.addClass('shown');
            $('.preloader').hide();
         });
         //row.child( row.data().msisdn );
      } else {
         row.child.show();
         elem.addClass('shown');
      }
   }
}
$(document).ready(function() {
   var configSelect = {
      valueField: 'msisdn',
      labelField: 'msisdn',
      searchField: 'msisdn',
      options: [],
      create: false,
      render: {
         option: function(item, escape) {
            return '<p>' + item.msisdn + '</p>';
         }
      },
      load: function(query, callback) {
         if (!query.length) return callback();
         $.ajax({
            headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            url: 'view/reports/fiber_installations/get-dns',
            type: 'POST',
            dataType: 'json',
            data: {
               q: query
            },
            error: function() {
               callback();
            },
            success: function(res) {
               if (res.success) callback(res.dns);
               else callback();
            }
         });
      }
   };
   $('#msisdn_select').selectize(configSelect);
   $('#status').selectize();
   $('#coverage_area').selectize({
      valueField: 'id',
      labelField: 'name',
      searchField: 'name'
   });
   var config = {
      autoclose: true,
      format: 'dd-mm-yyyy',
      todayHighlight: true,
      language: 'es',
      startDate: flimit,
      endDate: new Date()
   }
   $('#dateb').datepicker(config).on('changeDate', function(selected) {
      var dt = $('#datee').val();
      if (dt == '') {
         $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
      } else {
         var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
         if (diff > maxdays) {
            $('#datee').datepicker('update', sumDays($('#dateb').datepicker('getDate'), maxdays));
         }
      }
      var diff2 = getDateDiff($('#datee').datepicker('getDate'), flimit);
      if (diff2 > 0) {
         $('#datee').datepicker('update', flimit);
      }
      var maxDate = new Date(selected.date.valueOf());
      $('#datee').datepicker('setStartDate', maxDate);
   });
   //config.endDate = new Date(new Date().setTime(new Date().getTime()- (24*60*60*1000)));
   config.endDate = new Date(new Date().setTime(new Date().getTime() + (30 * 24 * 60 * 60 * 1000)));
   $('#datee').datepicker(config).on('changeDate', function(selected) {
      var dt = $('#dateb').val();
      if (dt == '') {
         $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
      } else {
         var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
         if (diff > maxdays) {
            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -maxdays));
         }
      }
      var diff2 = getDateDiff($('#dateb').datepicker('getDate'), flimit);
      if (diff2 > 0) {
         $('#dateb').datepicker('update', flimit);
      }
      var maxDate = new Date(selected.date.valueOf());
      $('#dateb').datepicker('setEndDate', maxDate);
   });
   $('#search').on('click', function(e) {
      $('.preloader').show();
      if ($.fn.DataTable.isDataTable('#list-com')) {
         $('#list-com').DataTable().destroy();
      }
      var table = $('#list-com').DataTable({
         searching: true,
         processing: true,
         serverSide: true,
         ajax: {
            url: "reports/fiber_installations/get-dt",
            data: function(d) {
               d._token = $('meta[name="csrf-token"]').attr('content');
               d.dateb = $('#dateb').val();
               d.datee = $('#datee').val();
               d.msisdn_select = getSelectObject('msisdn_select').getValue();
               d.status = $('#status').val();
               d.coverage_area = $('#coverage_area').val();
            },
            type: "POST"
         },
         initComplete: function(settings, json) {
            $(".preloader").fadeOut();
            $('#rep-sc').attr('hidden', null);
         },
         deferRender: true,
         ordering: false,
         columns: [{
            data: null,
            render: function(data, type, row, meta) {
               html = '';
               html = html + '<div data-id="' + row.id + '" class="details-control" onclick="viewDatails(' + row.id + ')"></div>';
               return html;
            },
            searchable: false,
            orderable: false
         }, {
            data: 'group_install',
            searchable: false,
            orderable: false,
            visible: false
         }, {
            data: null,
            searchable: true,
            orderable: false,
            render: function(data, type, row, meta) {
               return (row.msisdn ? row.msisdn : 'N/A');
            }
         }, {
            data: 'client',
            searchable: true,
            orderable: false
         }, {
            data: 'client_email',
            searchable: true,
            orderable: false
         }, {
            data: 'client_phone',
            searchable: true,
            orderable: false
         }, {
            data: 'address_instalation',
            searchable: false,
            orderable: false
         }, {
            data: 'seller',
            searchable: false,
            orderable: false
         }, {
            data: 'installer',
            searchable: false,
            orderable: false
         }, {
            data: 'installer_phone',
            searchable: false,
            orderable: false
         }, {
            data: 'zone_name',
            searchable: false,
            orderable: false
         }, {
            data: 'date_presell',
            searchable: false,
            orderable: false
         }, {
            data: null,
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
               return (row.date_install ? row.date_install : 'N/A');
            }
         }, {
            data: null,
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
               return (row.paid == 'N' ? 'No' : 'Si');
            }
         }, {
            data: null,
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
               switch (row.status) {
                  case 'A':
                     return 'En proceso';
                     break;
                  case 'R':
                     return 'Reprogramado';
                     break;
                  case 'P':
                     return 'Instalado';
                     break;
                  case 'T':
                     return 'Eliminado';
                     break;
               }
            }
         }, {
            data: 'num_rescheduling',
            searchable: false,
            orderable: false
         }, {
            data: 'reason_delete',
            searchable: false,
            orderable: false,
            render: function(data, type, row, meta) {
               return (row.reason_delete ? row.reason_delete : 'N/A');
            }
         }]
      });
   });
   $('#download').on('click', function(e) {
      var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');
      $(".preloader").fadeIn();
      $.ajax({
         type: "POST",
         url: "reports/fiber_installations/download_dt",
         data: data,
         dataType: "text",
         success: function(response) {
            $(".preloader").fadeOut();
            swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
         },
         error: function(err) {
            $(".preloader").fadeOut();
            swal('Error', 'No se pudo generar el reporte.', 'error');
         }
      });
   });
});