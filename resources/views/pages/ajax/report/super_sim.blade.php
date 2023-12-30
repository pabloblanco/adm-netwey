{{--/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Junio 2021
 */--}}
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">
                Reporte de super sim
            </h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li>
                    <a href="#">
                        Reportes
                    </a>
                </li>
                <li class="active">
                    Reporte de super sim
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="container report-conciliacion">
    <div class="row justify-content-center">
        <div class="col-12 pb-5">
            <h3>
                Configuración del reporte
            </h3>
            <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
                {{ csrf_field() }}
                <div class="row justify-content-center">   
                
                <div class="col-md-4 col-12">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Inicio
                        </label>
                        <div class="input-group">

                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateStar" name="dateStar" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y', strtotime('- 90 days', time())) }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                    <p>Solo se ver&aacute;n datos desde 17/Mayo/2021</p>
                </div>
                <div class="col-md-4 col-12 px-md-5">
                    <div class="form-group">
                        <label class="control-label">
                            Fecha Fin
                        </label>
                        <div class="input-group">
                            <input class="form-control" data-date-format="dd-mm-yyyy" id="dateEnd" name="dateEnd" placeholder="dd-mm-yyyy" type="text" value="{{ date('d-m-Y') }}">
                                <span class="input-group-addon">
                                    <i class="icon-calender">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-12">
                    <div class="form-group">
                        <label class="control-label">
                            DN
                        </label>
                        <div class="input-group">
                            <input class="form-control" id="DN" name="DN" placeholder="0000000000" type="text" minlength="10" maxlength="10">
                                <span class="input-group-addon">
                                    <i class="icon-call">
                                    </i>
                                </span>
                            </input>
                        </div>
                    </div>
                </div>
                </div>
                <div class="col-md-12 text-center">
                    <button class="btn btn-success" id="search" name="search" type="button">
                        <i class="fa fa-check">
                        </i>
                        Mostrar Reporte
                    </button>
                </div>
            </form>
        </div>
        <div class="col-12" hidden="" id="rep-sc">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de super sim
                    </h3>
                </div>
                <div class="col-md-12">
                    <button class="btn btn-success m-b-20" id="download" type="button">
                        Exportar reporte
                    </button>
                </div>
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped display nowrap" id="list-com">
                            <thead>
                                <tr>
                                    <th>
                                        DN
                                    </th>
                                    <th>
                                        Cliente
                                    </th>
                                    <th>
                                        Email del cliente
                                    </th>
                                    <th>
                                        Vendedor
                                    </th>
                                    <th>
                                        Email del vendedor
                                    </th>
                                    <th>
                                        Monto de la recarga
                                    </th>
                                    <th>
                                        Servicio recargado
                                    </th>
                                    <th>
                                        Numero de recarga
                                    </th>
                                    <th>
                                        Fecha de recarga
                                    </th>
                                    <th>
                                        Venta
                                    </th>
                                    
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/reports/rangePicker.js">
</script>
<script src="js/reports/super_sim.js">
</script>