<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Listado de usuarios</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Vendedores</a></li>
                <li class="active">C&oacute;digo Dep&oacute;sito</li>
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
                        @if($type == 'A') Usuarios @else Vendedores eliminados @endif con c&oacute;digo de dep&oacute;sito
                    </h3>
                    {{-- <button class="btn btn-success" id="btn-addDepositId" type="button">
                        Asignar C&oacute;digo
                    </button> --}}
                    <button class="btn btn-success" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12 p-t-20">
                    <div class="table-responsive">
                        <table id="listuserD" class="table table-striped">
                            <thead>
                                <tr>
                                    {{-- @if(!empty($canEdit) && $canEdit && $type=='A')
                                    <th>Acciones</th>
                                    @endif --}}
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Cod. Dep&oacute;sito</th>
                                    <th>Banco</th>
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

<div class="modal modalAnimate" id="addDepositID" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#addDepositID">&times;</button>
                <h4 class="modal-title"> @if($type == 'A') Usuario @else Vendedor @endif</h4>
            </div>

            <div class="modal-body">
                <form id="formDepositId" name="formDepositId" method="POST">
                    <div class="form-group">
                        <select id="userS" name="userS" class="form-control" placeholder="Seleccione un Usuario" data-msg="Debe seleccionar un usuario." required>
                            <option value="">Seleccione un @if($type == 'A') usuario @else vendedor @endif</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <input type="Text" name="cod" id="cod" class="form-control" placeholder="xx1234">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" id="saveFormDeposit" class="btn btn-success">guardar</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        {{--$("#btn-addDepositId").on('click',()=>{
            $('#addDepositID').modal({backdrop: 'static', keyboard: false});
        });

        function clearForm(){
            if($('#userS').selectize())
                $('#userS').selectize()[0].selectize.clear();

            $('#cod').val('');
        }

        var configSelect = {
            valueField: 'email',
            labelField: 'username',
            searchField: 'username',
            options: [],
            create: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+item.name+' '+item.last_name+'</p>';
                }
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{route("get_user_by_deposit")}}',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        q: query,
                        t: "{{$type}}"
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res){
                        if(res.success)
                            callback(res.users);
                        else
                            callback();
                    }
                });
            }
        };

        $.validator.methods.codDep = function( value, element ) {
            return this.optional(element)||/^[a-z]{2}\d{4}$/i.test(value);
        }--}}

        $('#download').on('click', function(){
            $(".preloader").fadeIn();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                type: "POST",
                url: "{{route('download_cod_dep_users')}}",
                dataType: "json",
                data: {
                    type: "{{$type}}"
                },
                success: function(response){
                    $(".preloader").fadeOut();

                    var a = document.createElement("a");
                        a.target = "_blank";
                        a.href = "{{route('downloadFile',['delete' => 1])}}?p=" + response.url;
                        a.click();
                },
                error: function(err){
                    $(".preloader").fadeOut();
                }
            });
        });

        {{-- $('#formDepositId').validate({
            rules: {
                userS: {
                    required: true
                },
                cod: {
                    required: true,
                    codDep: true
                }
            },
            messages: {
                user: {
                    required: "Debe seleccionar un usuario."
                },
                cod: {
                    required: "Debe escribir un código.",
                    codDep: "Debe ingresar un código válido."
                }
            }
        });

        $('#userS').selectize(configSelect);

        $('#saveFormDeposit').on('click', function(e){
            if($('#formDepositId').valid()){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('create_id_dep')}}",
                    method: 'POST',
                    data: $('#formDepositId').serialize(),
                    dataType: 'json',
                    success: function (res) {
                        if(res.success){
                            $('#addDepositID .close').trigger('click');

                            clearForm();

                            drawTable();
                        }else{
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        alert('No se pudo crear el id del depósito.');
                    }
                });
            }
        });

        $('#addDepositID').on('hidden.bs.modal', function(e){
            clearForm();
        });

        @if(!empty($canEdit) && $canEdit && $type=='A')
        deleteCodDep = function(e){
            var ud = $(e.currentTarget).data('ud');

            if(ud && ud != ''){
                if (confirm('¿Esta seguro de eliminar el código del usuario?')){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('delete_id_dep')}}",
                        method: 'POST',
                        data: {codigo: ud},
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

        editCodDep = function(e){
            var ud = $(e.currentTarget).data('ud');

            if(ud && ud != ''){
                swal("Escriba el nuevo código", {
                    content: {
                        element: "input",
                        attributes: {
                          placeholder: "Código ...",
                          type: "Text",
                          value: ""
                        },
                    },
                    closeOnClickOutside: false,
                    closeOnEsc: false,
                })
                .then((value) => {
                    if(value && value != '' && /^[a-z]{2}\d{4}$/i.test(value)){
                        $.ajax({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            async: true,
                            url: "{{ route('edit_id_dep') }}",
                            method: 'POST',
                            data: {userS: ud, cod: value},
                            dataType: 'json',
                            success: function(res){
                                if(res.success)
                                    drawTable();
                                else
                                    alert(res.msg);
                            },
                            error: function (res) {
                                swal({ text: "No se pudo editar el código de depósito, por favor intente mas tarde."});
                            }
                        });
                    }if(value == ''){
                        alert('Debe ingresar el nuevo código de depósito.');
                    }else if(!/^[a-z]{2}\d{4}$/i.test(value)){
                        swal({ text: "Debe ingresar un código valido. Ejm: xx1234"});
                    }
                });
            }
        }
        @endif

        @if(!empty($canEdit) && $canEdit && $type=='A')
            @php
                $norder = 4;
            @endphp
        @else
            @php
                $norder = 3;
            @endphp
        @endif --}}

        drawTable = function(){
            if ($.fn.DataTable.isDataTable('#listuserD')){
                $('#listuserD').DataTable().destroy();
            }

            $('.preloader').show();

            $('#listuserD').DataTable({
                searching: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('get_users')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.type = "{{$type}}";
                    },
                    type: "POST"
                },
                drawCallback:  function(settings, json){
                    {{-- @if(!empty($canEdit) && $canEdit && $type=='A')
                    $('.delete-ud').bind('click', deleteCodDep);
                    $('.edit-ud').bind('click', editCodDep);
                    @endif --}}
                    $(".preloader").fadeOut();
                },
                deferRender: true,
                order: [[ 4, "desc" ]],
                columns: [
                    {{--@if(!empty($canEdit) && $canEdit && $type=='A')
                    {data: null, render: function(data,type,row,meta){
                        var html = '<button type="button" class="btn btn-info btn-md edit-ud" data-ud="'+row.email+'" style="width: 80px;">Editar</button>';
                        html += '<button type="button" class="btn btn-danger btn-md delete-ud" data-ud="'+row.id+'" style="width: 80px;">Eliminar</button>';

                        return html;
                    }, searchable: false, orderable: false},
                    @endif--}}
                    {data: 'name', searchable: true},
                    {data: 'email', searchable: true},
                    {data: 'id_deposit', searchable: true},
                    {data: 'bank', searchable: false, orderable:false},
                    {data: 'date_reg', searchable: false}
                ]
            });
        }

        drawTable();
    });
</script>
<script src="js/common-modals.js"></script>