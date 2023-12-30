{{--
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Abril 2022
 --}}
<form id="form-portability" method="POST" name="form-portability">
  <div class="row justify-content-center">
    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          MSISDN Transitorio:
        </label>
        <div class="input-group">
          <input class="form-control" disabled="" id="dnTransitorio" name="dnTransitorio" type="text" value="{{$DNTrans}}"/>
        </div>
      </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          MSISDN a portar:
        </label>
        <input class="form-control" disabled="" id="dnPort" name="dnPort" type="text" value="{{$DNPort}}"/>
      </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-12" id="inputOperator">
      <div class="form-group">
        <label class="control-label">
          Operador de origen:
        </label>
        <select class="form-control" id="operator" name="operator">
          <option value="">
            Seleccione un operador
          </option>
          @foreach ($infoCompany as $status)
          <option value="{{$status->id}}">
            {{$status->name}}
          </option>
          @endforeach
        </select>
      </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-12" id="inputNip">
      <div class="form-group">
        <label class="control-label">
          Codigo NIP:
        </label>
        <input autocomplete="off" class="form-control" id="PortNIP" name="PortNIP" placeholder="Ingresa el codigo NIP" type="text"/>
      </div>
    </div>
    <div class="col-xl-3 col-lg-4 col-md-6 col-12" id="inputNip2">
      <div class="form-group">
        <label class="control-label">
          Confirmar Codigo NIP:
        </label>
        <input autocomplete="off" class="form-control" id="PortNIP2" name="PortNIP2" placeholder="Confirma el codigo NIP" type="text"/>
      </div>
    </div>
    <div class="col-12 pt-3 pb-5" id="noteNip">
      <strong>
        Nota:
      </strong>
      <span>
        El codigo NIP tiene una validez de 5 dias, puede solicitarse desde el equipo del cliente a portar via SMS con la palabra NIP al 051 o llamando al 051
      </span>
    </div>
    <hr/>
    <div class="col-12">
    </div>
    <div class="col-md-4 col-sm-6 col-12 text-center" id="BtnReNewDN">
      <button class="btn btn-danger btn-md button" id="RenewDN" type="button">
        <font size="2">
          Utilizar otro DN a portar
        </font>
      </button>
    </div>
    <div class="col-md-4 col-sm-6 col-12 text-center" id="BtnSend">
      <button class="btn btn-info btn-md button" id="newImportacion" type="button">
        <font size="2">
          Solicitar portación
        </font>
      </button>
    </div>
  </div>
</form>
<button class="mt-5 col-3 btn btn-info btn-md button" data-stepport="2" id="updatePort" type="button">
  <font size="1">
    Refrescar
  </font>
</button>
<script src="js/portabilidad/newImportacion.js?v=0.1">
</script>
