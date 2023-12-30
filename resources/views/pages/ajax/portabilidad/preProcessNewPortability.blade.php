{{--
Autor: Ing. Luis J. https://www.linkedin.com/in/ljpd2009
Abril 2022
 --}}
@if(empty($inprocess) && $salesP)
<form id="Preform-portability" method="POST" name="Preform-portability">
  <div class="row justify-content-center">
    <div class="col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          MSISDN Transitorio:
        </label>
        <div class="input-group">
          <input class="form-control" disabled="" id="dnTransitorio" name="dnTransitorio" type="text" value="{{$DNTrans}}"/>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          MSISDN a portar:
        </label>
        <input autocomplete="off" class="form-control" id="dnPort" name="dnPort" placeholder="Ingresa el DN a portar" type="text"/>
      </div>
    </div>
    <hr/>
    <div class="col-12">
    </div>
    <div class="col-md-4 col-sm-6 col-12 text-center" id="BtnVerify">
      <button class="btn btn-info btn-md button" id="verifyDN" type="button">
        <font size="2">
          Verificar DN a portar
        </font>
      </button>
    </div>
    <div class="col-md-4 col-sm-6 col-12 text-center" id="BtnNewDN">
      <button class="btn btn-danger btn-md button" id="newDN" type="button">
        <font size="2">
          Verificar otro DN a portar
        </font>
      </button>
    </div>
    <div class="col-md-4 col-sm-6 col-12 text-center" id="BtnnextPort">
      @php
      if(empty($permitirPort)){
        if(empty($infoJefe) && empty($sinSuperior)){
            @endphp
        {{--permitido SIN clave--}}
      <button class="btn btn-info btn-md button" id="nextPort" type="button">
        <font size="2">
          Continuar
        </font>
      </button>
      @php } elseif(!empty($infoJefe)) { @endphp
      {{--permitido CON clave--}}
      <button class="btn btn-info btn-md button" id="nextPortPass" onclick="authorizePort({{$infoJefe}})" type="button">
        <font size="2">
          Continuar
        </font>
      </button>
      @php } if(!empty($sinSuperior)) { @endphp
      {{--permitido pero sin supervisor--}}
      <button class="btn btn-info btn-md button" id="nextPortNotSup" onclick="infoAlert('{{$sinSuperior}}')" type="button">
        <font size="2">
          Continuar
        </font>
      </button>
      @php } } else { @endphp
            {{-- NO permitido--}}
      <button class="btn btn-info btn-md button" id="nextPortNot" onclick="infoAlert('{{$permitirPort}}')" type="button">
        <font size="2">
          Continuar
        </font>
      </button>
      @php } @endphp
    </div>
  </div>
</form>
<button class="mt-5 col-3 btn btn-info btn-md button" data-stepport="1" id="updatePort" type="button">
  <font size="1">
    Refrescar
  </font>
</button>
<script src="js/portabilidad/newImportacion.js?v=0.1">
</script>
@else
<div>
  <label>
    @if(!$salesP)
    Hubo un problema para obtener el registro de venta del DN {{$DNTrans}}
    @else
    El msisdn transitorio {{$DNTrans}} se encuentra en un proceso de portación activo desde {{$inprocess->date_reg}}. Aproximandamente en 3 días hábiles la portación debe estar completada.
    @endif
  </label>
</div>
<button class="mt-5 col-3 btn btn-info btn-md button" data-stepport="0" id="updatePort0" onclick="getTabPortability()" type="button">
  <font size="1">
    Refrescar
  </font>
</button>
@endif
