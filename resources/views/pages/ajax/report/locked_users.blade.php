<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de usuarios bloqueados</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de usuarios bloqueados</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Usuario</label>
                        <select id="userLocked" name="userLocked" class="form-control">
                            <option value="">Seleccione un usuario</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Estatus</label>
                        <select id="statusLock" name="statusLock" class="form-control">
                            <option value="all">Todos</option>
                            <option value="locked">Bloqueados</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha desde</label>
                        <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d', strtotime('- 90 days', time())) }}" readonly="true">
                    </div>
                </div>

                <div class="col-md-3 col-sm-6">
                    <div class="form-group">
                        <label class="control-label">Fecha hasta</label>
                        <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{ date('Y-m-d') }}" readonly="true">
                    </div>
                </div>
            </div>

            <div class="col-md-12 p-t-20 text-center">
                <div class="form-group">
                    <button class="btn btn-success" id="search" type="button">
                        Buscar
                    </button>
                </div>
            </div>
            </form>
        </div>

        <div class="col-md-12 col-sm-12" id="rep-ub" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de usuarios bloqueados
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-users-lock" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Usuario bloqueado</th>
                                    <th>Usuario que lo bloqueo</th>
                                    <th>Usuario que lo desbloqueo</th>
                                    <th>Fecha del bloqueo</th>
                                    <th>Fecha del desbloqueo</th>
                                    <th>D&iacute;as bloqueado</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        var config = {
            autoclose: true,
            format: 'yyyy-mm-dd',
            todayHighlight: true
        }

        $('#dateb').datepicker(config)
                .on('changeDate', function(selected){
                    var dt = $('#datee').val();
                    if(dt == ''){
                        $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 90));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), $('#datee').datepicker('getDate'));
                        if(diff > 90)
                            $('#datee').datepicker('setDate', sumDays($('#dateb').datepicker('getDate'), 90));
                    }
                });

        $('#datee').datepicker(config)
                .on('changeDate', function(selected){
                    var dt = $('#dateb').val();
                    if(dt == ''){
                        $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -90));
                    }else{
                        var diff = getDateDiff($('#dateb').datepicker('getDate'), selected.date);
                        if(diff > 90)
                            $('#dateb').datepicker('update', sumDays($('#datee').datepicker('getDate'), -90));
                    }
                });

        ajax1 = function(query, callback) {
                    if (!query.length) return callback();
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{route("getFilterUsersSellers")}}',
                        type: 'POST',
                        dataType: 'json',
                        cache: false,
                        data: {
                            name: query
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

        $('#userLocked').selectize({
            valueField: 'email',
            labelField: 'username',
            searchField: 'username',
            options: [],
            create: false,
            persist: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+item.name+' '+item.last_name+'</p>';
                }
            },
            load: ajax1
        });

        $('#statusLock').selectize();

        $('#search').on('click', function(e){
            $('.preloader').show();

            if ($.fn.DataTable.isDataTable('#list-users-lock')){
                $('#list-users-lock').DataTable().destroy();
            }

            $('#list-users-lock').DataTable({
                searching: false,
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{route('getUsersLDt')}}",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        d.userLocked = $('#userLocked').val();
                        d.statusLock = $('#statusLock').val();
                    },

                    type: "POST"
                },
                initComplete: function(settings, json){
                    $(".preloader").fadeOut();
                    $('#rep-ub').attr('hidden', null);
                },
                deferRender: true,
                order: [[ 3, "desc" ]],
                columns: [
                    {data: 'name_user', orderable: false, orderable: false},
                    {data: 'name_dolockuser', searchable: false, orderable: false},
                    {data: 'name_dounlockuser', searchable: false, orderable: false},
                    {data: 'date_locked', searchable: false},
                    {data: 'date_unlocked', searchable: false},
                    {data: 'days', searchable: false, orderable: false}
                ]
            });
        });

        $('#download').on('click', function(e){
            var data = $("#filterConc").serialize() + '&_token=' + $('meta[name="csrf-token"]').attr('content');

            $(".preloader").fadeIn();

            $.ajax({
                type: "POST",
                url: "{{route('downloadgetUsersLDt')}}",
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
</script>