@php
  // $flimit='2020-01-01 00:00:00';

  // $fini = date('d-m-Y', strtotime('- 30 days', time()));
  // if(strtotime($fini) < strtotime($flimit))
  //     $fini = date('d-m-Y',strtotime($flimit));

  // $fend = date('d-m-Y', strtotime('- 0 days', time()));
  // if(strtotime($fend) < strtotime($flimit))
  //     $fend = date('d-m-Y',strtotime($flimit));

  $accessPermission = 0;
  // $validatePermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'EIV-GIP')
      $accessPermission = $policy->value;
    // if ($policy->code == 'EIV-VEI')
    //   $validatePermission = $policy->value;
  }
@endphp

<style type="text/css">
    td.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  @if ($accessPermission > 0)
    <div class="container-fluid">
      <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
          <h4 class="page-title">Guias Pendientes por Procesar</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
          <ol class="breadcrumb">
            <li><a href="/islim/">Dashboard</a></li>
            <li class="active">Guias Pendientes</li>
          </ol>
        </div>
      </div>
    </div>
    <div class="container">
      <div class="row">
        <div class="col-md-12">
          <div class="row white-box">
            <div class="table-responsive">
              <table id="myTable" class="table table-striped">
                <thead>
                  <tr>
                    <th></th>
                    {{-- <th>Id</th> --}}
                    <th>Guia</th>
                    <th>Archivo</th>
                    <th>Fecha</th>
                    <th>Acci&oacute;n</th>
                  </tr>
                </thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <script type="text/javascript">
      actionPenddingOrders = (folio,type) => {
        $('.preloader').show();

        params={
          folio:folio,
          type:type
        };

        request ('{{route("actionPenddingOrders")}}', 'POST', params,
          function (res) {
              if ( res.success ) {
                if(res.total == 'Y'){
                  title = 'Felicidades';
                  icon = "success";

                  $('.container-action-btns[data-guia="'+folio+'"]').remove();
                }
                else{
                  title = 'Atención';
                  icon = "warning";
                }
                swal({
                  title: title,
                  text: res.msg,
                  icon: icon,
                });
                //$('.swal-text').html(res.msg);
                console.log('success', res);
              } else {

                swal({
                  title: "Atención",
                  text: res.msg,
                  icon: "error",
                });
                console.log('error', res.msg);
              }
              $('.preloader').hide();
          },
          function (res) {
              console.log('error', res.errorMsg);
              $('.preloader').hide();
        });
      }


      $(document).ready(function () {

        var table = $('#myTable').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            iDisplayLength: 10,
            ajax: {
                url: "{{route('getDtPenddingOrders')}}",
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
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
                {data: null, orderable: false, className:'details-control', defaultContent: ''},
                // {data: 'id', searchable: false, orderable: false},
                {data: 'folio', searchable: false, orderable: false},
                {data: 'file_name', searchable: false, orderable: false},
                {data: 'date_reg', searchable: false, orderable: false},
                {data: null, render: function(data,type,row,meta){

                    var html = '<div class="container-action-btns" data-guia="'+row.folio+'">';

                    html += '<button title="Aceptar Guia y Asignar al Coordinador" type="button" class="btn btn-success btn-md" style="width: 40px; padding: 0.4rem 1rem;" onclick="actionPenddingOrders(\''+row.folio+'\',\'A\')"><i class="fas fa-check-circle"></i></button>';

                    html += '<button title="Rechazar Guia y Asignar al Regional" type="button" class="btn btn-danger btn-md" style="width: 40px; padding: 0.4rem 1rem;" onclick="actionPenddingOrders(\''+row.folio+'\',\'R\')"><i class="fas fa-times-circle"></i></button>';

                    html += '</div>';
                    return html;

                }, searchable: false, orderable: false, width:"130px"},
            ]
        });


         // Add event listener for opening and closing details
        $('#myTable tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );

            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                if($(this).data('fill')!='Y'){
                    params={
                        folio:row.data().folio
                    };

                    elem=$(this);

                    $('.preloader').show();
                    request ('{{route("getDtPenddingOrderDetails")}}', 'POST', params,
                      function (res) {
                          if ( res.success ) {
                              elem.data('fill','Y');
                              row.child( res.msg );
                          } else {
                              row.child( 'Ocurrio un error en la consulta' );
                              console.log('error', res.errorMsg);
                          }
                          row.child.show();
                          tr.addClass('shown');
                          tr.next('tr').children('td').addClass('p-0');
                          $('.preloader').hide();
                      },
                      function (res) {
                          row.child( 'Ocurrio un error en la consulta' );
                          console.log('error', res.errorMsg);
                          row.child.show();
                          tr.addClass('shown');
                          $('.preloader').hide();
                    });
                }
                else{
                    row.child.show();
                    tr.addClass('shown');
                }
            }
        } );
      });
  </script>
  @else
    <h3>Lo sentimos, usted no posee permisos suficientes para acceder a este módulo</h3>
  @endif