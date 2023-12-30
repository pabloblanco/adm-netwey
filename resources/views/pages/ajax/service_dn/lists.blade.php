<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Listas de servicio por DN</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Canales</a></li>
                <li class="active">Lista</li>
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
                        Listas para servicios por DN
                    </h3>
                    <button class="btn btn-success" id="createList" type="button">
                        Crear lista
                    </button>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="listservice" class="table table-striped">
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

<div class="modal fade" id="editList" role="dialog">
    <div class="modal-dialog" id="modal01">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 id="modal-title" class="modal-title">Crear Lista</h4>
            </div>
            <div class="modal-body">
                <form id="listForm" method="POST" enctype='multipart/form-data'>
                    <div class="form-body">
                        <div class="row" id="dataList">
                            
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
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
                    url: '{{route("getDNs")}}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        q: query,
                        list: $('#list').val()
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

        deleteDn = function(e){
            var dn = $(e.currentTarget).data('dn');

            if(dn && dn != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('deleteDn')}}",
                    method: 'POST',
                    data: {dn: dn, list: $('#list').val()},
                    success: function (res) {
                        if(res.success){
                            loadLishtml(res);
                        }else{
                            alert('Ocurrio un error cargando la lista.');
                        }

                        $(".preloader").fadeOut();
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        alert('No se pudo eliminar el dn de la lista.');
                    }
                });
            }
        }

        deleteList = function(e){
            var list = $(e.currentTarget).data('list');

            if(list && list != ''){
                if (confirm('¿Esta seguro de eliminar la lista?')){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('deleteList')}}",
                        method: 'POST',
                        data: {list: list},
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

        drawTable = function(){
            if ($.fn.DataTable.isDataTable('#listservice')){
                $('#listservice').DataTable().destroy();
            }

            $('.preloader').show();

            var tableClients = $('#listservice').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('get_lists')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                    },
                    type: "POST"
                },
                initComplete: function(settings, json){
                    $('.delete-list').bind('click', deleteList);
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 2, "desc" ]],
                columns: [
                    {data: null, render: function(data,type,row,meta){
                        var html = '<button type="button" class="btn btn-info btn-md" data-list="'+row.id+'" data-toggle="modal" data-target="#editList">Editar</button>';
                        html += '<button type="button" class="btn btn-danger btn-md delete-list" data-list="'+row.id+'">Eliminar</button>';

                        return html;
                    }, searchable: false, orderable: false},
                    {data: 'name'},
                    {data: 'date_reg', searchable: false}
                ]
            });
        }

        loadLishtml = function(res){
            $('#dataList').html(res.html);

            if ($.fn.DataTable.isDataTable('#listdns')){
                $('#listdns').DataTable().destroy();
            }

            $('#listdns').DataTable({
                language: {
                    url: "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
                },
                columnDefs: [
                    {
                        targets: 0,
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [[ 1, "desc" ]]
            });

            $('#dns').selectize(configSelect);

            $('#saveEditList').bind('click', savelist);

            $('.delete-ldn').bind('click', deleteDn);

            $('#dnm').bind('click', inDN);

            if(res.noUpdate && res.noUpdate.length){
                alert('El o Los siguiente(s) DN(s) ya pertenecen a una lista: '+res.noUpdate.toString());
            }
        }

        inDN = function(e){
            $('#msisdn_file').val('');
            
            if($('#dns').selectize())
                $('#dns').selectize()[0].selectize.clear();

            if($('#dnm').is(':checked')){
                $('#file-content').addClass('hidden');
                $('#dn-content').removeClass('hidden');
            }else{
                $('#file-content').removeClass('hidden');
                $('#dn-content').addClass('hidden');
            }
        }

        savelist = function(e){
            $('.preloader').show();

            var formData = new FormData($('#listForm')[0]);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: "{{route('saveEdit')}}",
                method: 'POST',
                data: formData,
                mimeType: "multipart/form-data",
                processData: false,
                contentType: false,
                success: function (res) {
                    res = JSON.parse(res);
                    if(res.success){
                        loadLishtml(res);
                    }else{
                        if(res.msg)
                            alert(res.msg);
                        else
                            alert('Ocurrio un error cargando la lista.');
                    }

                    $(".preloader").fadeOut();
                },
                error: function (res) {
                    $(".preloader").fadeOut();
                    alert('No se pudo guardar la edicion de la lista.');
                }
            });
        }

        drawTable();

        $('#createList').on('click', function(e){
            swal("Escriba el nombre de la lista", {
                content: {
                    element: "input",
                    attributes: {
                      placeholder: "Lista ...",
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
                        url: "{{route('createList')}}",
                        method: 'POST',
                        data: {name: value},
                        success: function (res) {
                            drawTable();
                        },
                        error: function (res) {
                            alert('No se pudo crear la lista, por favor intente mas tarde.');
                        }
                    });
                }if(value == ''){
                    swal('Debe ingresar el nombre de la lista.');
                }
            });
        });

        $('#editList').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var recipient = button.data('list');

            if(recipient && recipient != ''){
                $('.preloader').show();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('getDatalist')}}",
                    method: 'POST',
                    data: {list: recipient},
                    success: function (res) {
                        if(res.success){
                            loadLishtml(res);
                        }else{
                            alert('Ocurrio un error cargando la lista.');
                        }

                        $(".preloader").fadeOut();
                    },
                    error: function (res) {
                        $(".preloader").fadeOut();
                        alert('Ocurrio un error cargando la lista.');
                    }
                });
            }
        });
    });
</script>