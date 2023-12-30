@php
    //menor fecha posible permitida
    $flimit='2022-01-01 00:00:00';
    $fini = date('d-m-Y', strtotime('- 30 days', time()));
    if(strtotime($fini) < strtotime($flimit))
        $fini = date('d-m-Y',strtotime($flimit));

    $fend = date('d-m-Y', strtotime('- 0 days', time()));
    if(strtotime($fend) < strtotime($flimit))
        $fend = date('d-m-Y',strtotime($flimit));
@endphp
<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }

    div.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
        display: list-item;
        padding-left: 15px !important;
        padding-right: 15px !important;
    }
    div.details-control.shown {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de instalaciones de fibra por estatus</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Instalaciones de fibra</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <form id="filterConc" name="filterConc" class=" text-left" method="POST">
            <div class="row">

                <div class="col-12 col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">Filtrar por</label>
                        <select id="dateFilter" name="dateFilter" class="form-control">
                            <option value="" selected>Selecciona</option>
                            <option value="date_sell">Fecha de venta (cita)</option>
                            <option value="date_activation">Fecha de instalación (Activación del servicio)</option>
                        </select>
                    </div>
                </div>

                <div class="col-12 col-md-4 col-sm-12">
                    <div class="form-group ">
                        <label class="control-label">Fecha de inicio</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy"  value="{{$fini}}" readonly>
                            <span class="input-group-addon">
                                <i class="icon-calender"></i>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">Fecha de fin</label>
                        <div class="input-group">
                            <input autocomplete="off" type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy" value="{{$fend}}" readonly>
                            <span class="input-group-addon">
                                <i class="icon-calender"></i>
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <div class="row">
                <div class="col-12 col-md-4 col-sm-12">
                    <div class="form-group">
                        <label class="control-label">Estado</label>
                        <select id="status" name="status" class="form-control">
                            <option value="" selected>Todos</option>
                            <option value="A">En proceso</option>
                            <option value="P">Instalado</option>
                            <option value="T">Eliminado</option>
                        </select>
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

        <div class="col-md-12 col-sm-12" id="rep-sc" hidden>
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de Instalaciones de Fibra
                    </h3>
                </div>

                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar Excel
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table id="list-com" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>msisdn</th>
                                    <th>MAC</th>
                                    <th>Cliente</th>
                                    <th>Venderdor</th>
                                    <th>Colonia</th>
                                    <th>Zona de Cobertura</th>
                                    <th>Estatus</th>
                                    <th>Re-programaciones</th>
                                    <th>Fecha venta</th>
                                    <th>Fecha instalación</th>
                                    <th>Antigüedad de venta</th>
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
        //maxima cant de dias permitidos entre las fechas de inicio y fin
        maxdays = 90;
        //menor fecha posible permitida
        flimit = new Date(Date.parse("{{$flimit}}"));
    });
</script>

<script src="js/reports/fiberInstallationByStatus.js"></script>
