
$(document).ready(function() {
   $('#status').selectize();
   $('#dateFilter').selectize();

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
      $(".preloader").fadeOut();

      if( $('#dateFilter').val() == ""){
         swal('Selecciona una opción en: "filtrar por"' , { icon: 'error' })
         return false;
      }

      if ($.fn.DataTable.isDataTable('#list-com')) {
         $('#list-com').DataTable().destroy();
      }
      
      var table = $('#list-com').DataTable({
         searching: false,
         processing: true,
         serverSide: true,
         ajax: {
            url: "api/reports/report_fiber_installations/get-by-status",
            data: function(d) {
               d._token = $('meta[name="csrf-token"]').attr('content'),
               d.dateb = $('#dateb').val(),
               d.datee = $('#datee').val(),
               d.status = $('#status').val(),
               d.dateFilter = $('#dateFilter').val()
            },
            type: "POST"
         },
         initComplete: function(settings, json) {
            $(".preloader").fadeOut();
            $('#rep-sc').attr('hidden', null);
         },
         deferRender: true,
         ordering: false,
         columns:
         [
            {
               data: null,
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  return (row.msisdn ? row.msisdn : 'N/A');
               }
            },
            {
               data: null,
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  return (row.mac ? row.mac : 'N/A');
               }
            }, 
            {
               data: 'client',
               searchable: true,
               orderable: false
            }, 
            {
               data: 'seller',
               searchable: false,
               orderable: false
            }, 
            {
               data: 'colony',
               searchable: false,
               orderable: false
            }, 
            {
               data: 'zone_name',
               searchable: false,
               orderable: false
            }, 
            {
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
            }, 
            {
               data: null,
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  return (row.num_rescheduling ? row.num_rescheduling : '0');
               }
            }, 
            {
               data: null, //Fecha de la venta
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  return (row.date_instalation ? row.date_instalation : 'N/A');
               }
            }, 
            {
               data: null, //Fecha de activacion de serv
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  return (row.date_activation ? row.date_activation : 'N/A');
               }
            }, 
            {
               data: null,
               searchable: false,
               orderable: false,
               render: function(data, type, row, meta) {
                  var html = "";
                  if (row.antiquity >= 8 && row.status == "A") {
                     html = "<p style='color:red; font-weight:700;' >" + row.antiquity + " días</p>";
                  } else {
                     html = "<p>" + row.antiquity + " días</p>";
                  }
                  return html;
               }
            }
         ]
      });
   });


   $('#download').on('click', function(e) {
      var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');
      $(".preloader").fadeIn();
      $.ajax({
         type: "POST",
         url: "api/reports/report_fiber_installations/download",
         data: data,
         dataType: "text",
         success: function(response) {
            console.log(response)
            $(".preloader").fadeOut();
            swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
         },
         error: function(err) {
            console.log(err)
            $(".preloader").fadeOut();
            swal('Error', 'No se pudo generar el reporte.', 'error');
         }
      });
   });
});