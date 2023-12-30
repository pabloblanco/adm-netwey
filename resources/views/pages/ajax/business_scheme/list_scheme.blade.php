{{--/*
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Abril 2022
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Esquema comercial
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Usuarios
          </a>
        </li>
        <li class="active">
          Esquema comercial
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-retention">
  <div class="row justify-content-center">
    <div class="col-12" id="create_scheme">
      @if($addScheme)
      <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" id="open_modal_btn" type="button">
        Agregar
      </button>
      @endif
      <hr/>
    </div>
    <div class="col-12 pb-5">
      <h3>
        Configuración del listado
      </h3>
      <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
        {{ csrf_field() }}
        <div class="row justify-content-center align-items-center">
          <div class="px-md-4 col-md-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Tipo
              </label>
              <select class="form-control" id="type" name="type">
                <option value="">
                  Seleccione un tipo
                </option>
                @foreach ($type as $status)
                <option value="{{$status['code']}}">
                  {{$status['description']}}
                </option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="px-md-4 col-md-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Nombre del esquema
              </label>
              <select class="form-control" id="nameScheme" name="nameScheme">
                <option value="">
                  Buscar por nombre
                </option>
              </select>
            </div>
          </div>
          <div class="col-12 text-center">
            <button class="btn btn-success" id="search" name="search" type="button">
              <i class="fa fa-check">
              </i>
              Mostrar listado
            </button>
          </div>
        </div>
      </form>
    </div>
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-12">
          <h3 class="text-center">
            Listado de esquema comercial
          </h3>
        </div>
        <div class="col-12">
          <div class="table-responsive">
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th class="text-center align-middle">
                    Acciones
                  </th>
                  <th class="text-center align-middle">
                    Tipo
                  </th>
                  <th class="text-center align-middle">
                    Nombre
                  </th>
                  <th class="text-center align-middle">
                    Responsable
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
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-modal="#myModal" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
          Creacion de nuevo item de esquema comercial
        </h4>
      </div>
      <div class="modal-body">
        <div class="row justify-content-center align-items-center">
          <div class="col-md-6 col-12">
            <div class="form-group">
              <label class="control-label">
                Que desea registrar
              </label>
              <select class="form-control" id="typeCreate" name="typeCreate">
                <option value="">
                  Seleccione un tipo
                </option>
                @foreach ($type as $status)
                <option value="{{$status['code']}}">
                  {{$status['description']}}
                </option>
                @endforeach
              </select>
            </div>
          </div>
        </div>
        <div id="blockNew" name="blockNew">
        </div>
      </div>
    </div>
  </div>
</div>
{{--
<script src="js/low/axios.min.js?v=0.19.2">
</script>
--}}
<script>
  var divList = [];
  var regiList = [];
  var coordList = [];
    @foreach ($Listdivision as $value)
      divList.push({code: "{{$value['code']}}", description: "{{$value['description']}}"});
    @endforeach

    @foreach ($ListRegion as $value)
      regiList.push({code: "{{$value['code']}}", description: "{{$value['description']}}"});
    @endforeach

    @foreach ($ListCoord as $value)
      coordList.push({code: "{{$value['code']}}", description: "{{$value['description']}}"});
    @endforeach
</script>
<script src="js/users/scheme.js">
</script>
<script src="js/common-modals.js">
</script>