<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Listado de canales</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Canales</a></li>
                <li class="active">lista de canales</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Canales para servicios por concentrador
                    </h3>
                    <button class="btn btn-success" id="createChannel" type="button">
                        Crear canal
                    </button>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="listchannels" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Acciones</th>
                                    <th>Nombre</th>
                                    <th>Fecha de registro</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {

        deleteChannel = function(e){
            var ch = $(e.currentTarget).data('ch');

            if(ch && ch != ''){
                if (confirm('¿Esta seguro de eliminar el canal?')){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('deleteChannel')}}",
                        method: 'POST',
                        data: {channel: ch},
                        success: function (res) {
                            drawTable();
                        },
                        error: function (res) {
                            alert('No se pudo eliminar la lista, por favor intente mas tarde.');
                        }
                    });
                }
            }
        }

        editChannel = function(e){
            var ch = $(e.currentTarget).data('ch'),
                name = $(e.currentTarget).data('name');

            if(ch && ch != '' && name && name != ''){
                swal("Escriba el nombre del canal", {
                    content: {
                        element: "input",
                        attributes: {
                          placeholder: "Canal ...",
                          type: "Text",
                          value: name
                        },
                    }
                })
                .then((value) => {
                    if(value && value != ''){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            async: true,
                            url: "{{route('editChannel')}}",
                            method: 'POST',
                            data: {channel: ch, name: value},
                            success: function (res) {
                                drawTable();
                            },
                            error: function (res) {
                                alert('No se pudo editar el canal, por favor intente mas tarde.');
                            }
                        });
                    }if(value == ''){
                        swal('Debe ingresar el nombre del canal.');
                    }
                });
            }
        }

        drawTable = function(){
            if ($.fn.DataTable.isDataTable('#listchannels')){
                $('#listchannels').DataTable().destroy();
            }

            $('.preloader').show();

            var tableClients = $('#listchannels').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('get_channles')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('.delete-ch').bind('click', deleteChannel);
                    $('.edit-ch').bind('click', editChannel);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 2, "desc" ]],
                columns: [
                    {data: null, render: function(data,type,row,meta){
                        var html = '<button type="button" class="btn btn-info btn-md edit-ch" data-ch="'+row.id+'" data-name="'+row.name+'">Editar</button>';
                        html += '<button type="button" class="btn btn-danger btn-md delete-ch" data-ch="'+row.id+'">Eliminar</button>';

                        return html;
                    }, searchable: false, orderable: false},
                    {data: 'name'},
                    {data: 'date_reg', searchable: false}
                ]
            });
        }

        drawTable();

        $('#createChannel').on('click', function(e){
            swal("Escriba el nombre del canal", {
                content: {
                    element: "input",
                    attributes: {
                      placeholder: "Canal ...",
                      type: "Text",
                    },
                }
            })
            .then((value) => {
                if(value && value != ''){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('createChannel')}}",
                        method: 'POST',
                        data: {name: value},
                        success: function (res) {
                            drawTable();
                        },
                        error: function (res) {
                            alert('No se pudo crear el canal, por favor intente mas tarde.');
                        }
                    });
                }if(value == ''){
                    swal('Debe ingresar el nombre del canal.');
                }
            });
        });
    });
</script>