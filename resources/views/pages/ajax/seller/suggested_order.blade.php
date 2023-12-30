<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Pedido sugerido</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Vendedores</a></li>
                <li class="active">Pedido sugerido</li>
            </ol>
        </div>
    </div>
</div>

<div class="container-fluid">
    <section class="m-t-40">
        <div class="row white-box">
            <div class="col-md-12">
                <h3 class="text-center">
                    Lista de coordinadores con pedido sugerido

                    <div class="alert alert-success m-t-10" style="padding: 5px;">
                      Ventas calculadas desde {{$dateB}} hasta {{$dateE}}
                    </div>
                </h3>

                
                {{-- <button class="btn btn-success" id="download" type="button">
                    Exportar Excel
                </button> --}}
            </div>
            <div class="col-md-12 p-t-20">
                <div class="table-responsive">
                    <table id="listCoord" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Coordinación</th>
                                <th>Producto</th>
                                <th>Ventas Totales</th>
                                <th>Vetas diarias (Promedio)</th>
                                <th>Stock disponible</th>
                                <th>Días desfase</th>
                                <th>Pedido sugerido</th>
                            </tr>
                        </thead>
                        <tbody class="list-content">
                          @foreach($users as $user)
                            <tr>
                              <td class="user-name" data-name="{{$user->name}} {{$user->last_name}}"> {{$user->name}} {{$user->last_name}} </td>
                              <td class="user" data-user="{{$user->email}}"> {{$user->email}} </td>
                              <td class="user-phone" data-phone="{{$user->phone}}"> {{$user->phone}} </td>
                              <td class="user-coord" data-coord="{{!empty($user->esquema) ? $user->esquema : 'S/I'}}"> {{!empty($user->esquema) ? $user->esquema : 'S/I'}} </td>
                              <td class="product" data-product="{{$user->product}}" data-productname="{{$user->article}}"> {{$user->article}} </td>
                              <td class="total-sales" data-totalsales="{{$user->totalSales}}"> {{$user->totalSales}} </td>
                              <td class="prom-sales" data-promsales="{{$user->promSales}}"> {{$user->promSales}} </td>
                              <td class="available-stock" data-avstock="{{$user->availableStock}}"> {{$user->availableStock}} </td>
                              <td> <input type="Text" name="gap" class="form-control gap" data-index="{{$loop->index}}" value="{{env('GAP_DAYS', 5)}}"> </td>
                              <td> <input type="Text" name="sug" class="form-control suggested" value="{{$user->sug}}"> </td>
                            </tr>
                          @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-12 text-right p-t-20">
              <button class="btn btn-success" id="download" type="button">
                Guardar y descargar Listado
              </button>
            </div>
        </div>
    </section>
</div>

<script type="text/javascript">
    $(document).ready(function () {
      $('#listCoord').DataTable({
        searching: false,
        processing: true,
        serverSide: false,
        paging: false,
        language: {
        	sProcessing:     "Procesando...",
          sLengthMenu:     "Mostrar _MENU_ registros",
          sZeroRecords:    "No se encontraron resultados",
          sEmptyTable:     "Ningún dato disponible en esta tabla",
          sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
          sInfoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
          sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
          sInfoPostFix:    "",
          sSearch:         "Buscar:",
          sUrl:            "",
          sInfoThousands:  ",",
          sLoadingRecords: "Cargando...",
          oPaginate: {
            sFirst:    "Primero",
            sLast:     "Último",
            sNext:     "Siguiente",
            sPrevious: "Anterior"
          },
          oAria: {
            sSortAscending:  ": Activar para ordenar la columna de manera ascendente",
            sSortDescending: ": Activar para ordenar la columna de manera descendente"
          }
        },
      });

      /**
      !!!OJO!!! Si se agregan columnas a la tabla listCoord, este metodo se debe modificar
      **/
      $('.gap').on('blur', function(){
        let days = {{env('STOCK_DAYS', 15)}};
        let table = $('#listCoord').DataTable();
        let index = $(this).data('index');
        let gap = parseInt($(this).val());

        if(gap && !isNaN(gap) && gap > 0){
          let prom = table.cell({row:index, column:6});
          prom = parseInt(prom.data())
          let stockA = table.cell({row:index, column:7});
          let sugfield = table.cell({row:index, column:9});
          let sug = (days * prom) - (parseInt(stockA.data()) - (gap * prom));
          if(sug < 0){
            sug = 0;
          }
          sugfield.data('<input type="Text" name="sug" class="form-control suggested" value="'+sug+'">');
        }

      });

      $('#download').on('click', function(e){
        $(".preloader").fadeIn();

        let list = $('.list-content tr');
        let listarr = []; 

        list.each(function(ele){
          let user = $(this).find( ".user" ).data('user');
          let product = $(this).find( ".product" ).data('product');
          let productname = $(this).find( ".product" ).data('productname');
          let totalsales = $(this).find( ".total-sales" ).data('totalsales');
          let promsales = $(this).find( ".prom-sales" ).data('promsales');
          let avstock = $(this).find( ".available-stock" ).data('avstock');
          let name = $(this).find( ".user-name" ).data('name');
          let phone = $(this).find( ".user-phone" ).data('phone');
          let coord = $(this).find( ".user-coord" ).data('coord');
          let gap = $(this).find( ".gap" ).val();
          let suggested = $(this).find( ".suggested" ).val();

          listarr.push({
            name: name,
            user: user,
            phone: phone,
            coord: coord,
            product: product,
            productname: productname,
            totalsales: totalsales,
            promsales: promsales,
            avstock: avstock,
            gap: gap,
            suggested: suggested
          });
        });

        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          type: "POST",
          url: "{{route('suggested_order_save')}}",
          dataType: "json",
          data: {data: listarr},
          success: function(response){
              $(".preloader").fadeOut();

              if(response.success){
                let a = document.createElement("a");
                    a.target = "_blank";
                    a.href = response.url;
                    a.click();

                alert('Se guardo exitosamente la sugerencia de pedido.');
              }
          },
          error: function(err){
              $(".preloader").fadeOut();
          }
        });
      });
    });
</script>