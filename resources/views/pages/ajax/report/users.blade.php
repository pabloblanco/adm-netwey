<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Estatus del vendedor</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reportes</a></li>
                <li class="active">Estatus del vendedor</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
  <div class="row">
    <div class="col-md-12">
      <h3>Configuración del reporte</h3>
    </div>
  </div>
  <div class="row">
    <div class="col-md-12">
     {{--  <form id="seller_status_form" method="GET" action="view/reports/seller/status">
      </form> --}}
      <div class="container">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
                <label class="control-label">Tipo</label>
                <select id="type" name="type" class="form-control">
                  <option value="">Seleccione un tipo de usuario</option>
                  @foreach ($types as $type)
                    <option value="{{$type['code']}}">{{$type['description']}}</option>
                  @endforeach
                </select>
            </div>
          </div>
          <div class="col-md-4 hidden">
            <div class="form-group">
                <label class="control-label">Estado</label>
                <select id="status" name="status" class="form-control" multiple>
                  <option value="">Seleccione un estado...</option>
                  @foreach ($status as $stat)
                    <option value="{{$stat['code']}}">{{$stat['description']}}</option>
                  @endforeach
                </select>
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
                <label class="control-label">Nombre</label>
                <select id="seller_name" name="seller_name" class="form-control">
                  <option value="">Seleccione el nombre de usuario</option>
                  @foreach ($sellers as $seller)
                    <option value="{{$seller->email}}">{{$seller->name}} {{$seller->last_name}}</option>
                  @endforeach
                </select>
            </div>
          </div>
          <div class="col-md-4 hidden">
            <div class="form-group">
                <label class="control-label">Correo electroníco</label>
                <select id="seller_email" name="seller_email" class="form-control" multiple>
                  <option value="">Seleccione el(los) correo(s) electrónico(s) de usuario...</option>
                  @foreach ($sellers as $seller)
                    <option value="{{$seller->email}}">{{$seller->email}}</option>
                  @endforeach
                </select>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label class="control-label">&nbsp;</label>
              <div class="input-group">
              <button type="button" class="btn btn-success" onclick="getReport();"> <i class="fa fa-check"></i> Generar reporte</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <hr>
      <div class="container" id="report_container">
      </div>
    </div>
  </div>
</div>
<script src="js/reports/users.js"></script>