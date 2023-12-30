@php
    $flimit='2020-01-01 00:00:00';
    $fini = date('d-m-Y', strtotime('- 30 days', time()));
    if(strtotime($fini) < strtotime($flimit))
        $fini = date('d-m-Y',strtotime($flimit));

    $fend = date('d-m-Y', strtotime('- 0 days', time()));
    if(strtotime($fend) < strtotime($flimit))
        $fend = date('d-m-Y',strtotime($flimit));

    $accessPermission = 0;
    $validatePermission = 0;
    foreach (session('user')->policies as $policy) {
      if ($policy->code == 'EIV-REI')
        $accessPermission = $policy->value;
      if ($policy->code == 'EIV-VEI')
        $validatePermission = $policy->value;
    }
@endphp
@if ($accessPermission > 0 || $validatePermission > 0)
    <link crossorigin="anonymous" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"
          integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ=="
          referrerpolicy="no-referrer" rel="stylesheet"/>
    <div class="container-fluid">
        <div class="row bg-title">
            <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
                <h4 class="page-title">
                    Reporte Merma Equipos viejos
                </h4>
            </div>
            <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
                <ol class="breadcrumb">
                    <li>
                        <a href="/islim/">
                            Dashboard
                        </a>
                    </li>
                    <li class="active">
                        Reporte Merma Equipos viejos
                    </li>
                </ol>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <form class=" text-left" id="filterConc" method="POST" name="filterConc">
                    <div class="row">
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Fecha desde
                                </label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control" id="dateb" name="dateb"
                                           placeholder="dd-mm-yyyy" type="text" value="{{ $fini }}">
                                        <span class="input-group-addon">
                                            <i class="icon-calender"></i>
                                        </span>
                                    </input>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">
                                    Fecha hasta
                                </label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control" id="datee" name="datee"
                                           placeholder="dd-mm-yyyy" type="text" value="{{ $fend }}">
                                        <span class="input-group-addon">
                                            <i class="icon-calender"></i>
                                        </span>
                                    </input>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                            <div class="form-group">
                                <label class="control-label">
                                    MSISDNS
                                </label>
                                <div class="input-group">
                                    <input autocomplete="off" class="form-control" id="msisdns" name="msisdns" placeholder="Ingresa MSISDNS separado por (,)" type="text">
                                    </input>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12 p-t-20 text-center">
                        <div class="form-group">
                            <button class="btn btn-success" id="search" type="button" onclick="searchStatus();">
                                Buscar
                            </button>
                            <button class="btn btn-success" id="downloadReport" type="button">
                                Generar Reporte
                            </button>
                        </div>
                    </div>

                </form>
            </div>
            <hr/>
            <div class="col-12 white-box">
                <div class="table-responsive">
                    <table class="table table-striped display nowrap" id="myTable">
                        <thead>
                        <tr>
                            <th>
                                MSISDN
                            </th>
                            <th>
                                Articulo
                            </th>
                            <th>
                                Supervisor
                            </th>
                            <th>
                                Vendedor
                            </th>
                            <th>
                                Fecha Asignacion
                            </th>
                            <th>
                                Fecha Rojo
                            </th>
                        </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        function searchStatus() {
            $('.preloader').show();
            if ($.fn.DataTable.isDataTable('#myTable')) {
                $('#myTable').DataTable().destroy();
            }
            $('#myTable').DataTable({

                ajax: {
                    url: "api/inventories/merma-old-equipment",
                    data: function (d) {
                        d._token = $('meta[name="csrf-token"]').attr('content');
                        d.dateb = $('#dateb').val();
                        d.datee = $('#datee').val();
                        if ( $('#msisdns').val() != '' ) {
                            d.msisdns = $('#msisdns').val();
                        }
                    },
                    type: "POST"
                },
                initComplete: function (settings, json) {
                    $(".preloader").fadeOut();
                    $('#rep-sc').attr('hidden', null);
                },
                searching: true,
                processing: true,
                serverSide: true,
                iDisplayLength: 20,
                deferRender: true,
                ordering: true,
                columns: [{
                    data: 'msisdn',
                    searchable: true,
                    orderable: false
                }, {
                    data: 'title',
                    searchable: true,
                    orderable: false
                }, {
                    data: 'name_supervisor',
                    searchable: true,
                    orderable: true
                }, {
                    data: 'name_seller',
                    searchable: true,
                    orderable: false
                },  {
                    data: 'first_assignment',
                    searchable: false,
                    orderable: false
                }, {
                    data: null,
                    render: function (data, type, row, meta) {
                        var html = '<span style="background: red;color: white;padding: 3px 10px;font-weight: bold;">' + row.date_red + '</span>';
                        return html;
                    },
                    searchable: false,
                    orderable: false,
                    width: "160px"
                },

                ]
            });
        }

        $('#downloadReport').on('click', function () {

            var params = new FormData();
            params.append('_token', $('meta[name="csrf-token"]').attr('content'));
            params.append('dateb', $('#dateb').val());
            params.append('datee', $('#datee').val());
            params.append('msisdns', $('#msisdns').val());

            $(".preloader").fadeIn();
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'view/reports/download-report-merma-old-equipment',
                type: 'post',
                data: params,
                contentType: false,
                processData: false,
                cache: false,
                async: true,
                success: function (res) {
                    swal('Generando reporte', 'El reporte estara disponible en unos minutos.', 'success');
                    $(".preloader").fadeOut();
                },
                error: function (res) {
                    swal('Generando reporte', 'Ha ocurrido un error al intentar generar el reporte.', 'error');
                    $(".preloader").fadeOut();
                }
            });
        })

        $(document).ready(function() {
            maxdays = 2 * 365;
            flimit = new Date(Date.parse("{{$flimit}}"));
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
            config.endDate = new Date(new Date().setTime(new Date().getTime()));
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
            $('#color_status').selectize();
            $('#search').on('click', function(e) {
                searchStatus();
            });
            $('#search').trigger('click');
            motive_btn = (motive, user_email, arti_detail, msisdn) => {
                switch (motive) {
                    case "valido":
                        txt = "un motivo válido";
                        url = "api/inventories/status/set-valid-motive";
                        break;
                    case "invalido":
                        txt = "un motivo no válido";
                        url = "api/inventories/status/set-invalid-motive";
                        break;
                    case "robo":
                        txt = "robo";
                        url = "api/inventories/status/set-theft-motive";
                        break;
                }
                swal({
                    title: "¿Desea realizar esta acción?",
                    text: "Se registrará que el DN #" + msisdn + " no se ha vendido por " + txt + ", esta acción no se podrá revertir",
                    icon: "warning",
                    buttons: {
                        cancel: "Cancelar",
                        accept: "Aceptar"
                    },
                }).then((accept) => {
                    if (accept) {
                        $('.preloader').show();
                        $.ajax({
                            url: url,
                            type: 'POST',
                            data: {
                                users_email: user_email,
                                inv_arti_details_id: arti_detail,
                                _token: $('meta[name="csrf-token"]').attr('content')
                            },
                            dataType: 'json',
                            success: function(result) {
                                if (result.success) {
                                    swal({
                                        title: 'Motivo registrado con éxito',
                                        text: "El DN #" + msisdn + " no se ha vendido por " + txt,
                                        icon: "success",
                                    });
                                    $('.container-motive-btns[data-id="' + user_email + '-' + arti_detail + '"]').addClass('d-none');
                                    $('.preloader').hide();
                                } else {
                                    swal({
                                        title: 'Ocurrio un error',
                                        text: 'por favor verifique e intente nuevamente',
                                        icon: "error",
                                    });
                                    $('.preloader').hide();
                                }
                            },
                            error: function(e) {
                                swal({
                                    title: 'Ocurrio un error',
                                    text: 'por favor intente nuevamente ',
                                    icon: "error",
                                });
                                console.log(e);
                                $('.preloader').hide();
                            }
                        });
                    }
                });
            }
        });
    </script>
    {{--
    <script src="js/statusinv/main.js?v=2.0" type="text/javascript">
    </script>
    --}}
@else
    <h3>
        Lo sentimos, usted no posee permisos suficientes para acceder a este módulo
    </h3>
@endif
