<div class="table-responsive">
  <table class="table table-striped display nowrap " id="clienteTable">
    <thead>
      <tr>
        <th>
          Acciones
        </th>
        <th>
          Nombre Cliente
        </th>
        <th>
          Email
        </th>
        <th>
          Teléfono de contacto
        </th>
        <th>
          Teléfono 2
        </th>
        <th>
          DN Netwey
        </th>
        <th>
          Tipo
        </th>
        <th>
          Servicialidad
        </th>
        <th>
          Plan activo
        </th>
        <th>
          Latitud
        </th>
        <th>
          Longitud
        </th>
        <th>
          Estatus
        </th>
      </tr>
    </thead>
  </table>
</div>
<script>
  var msisdns = new Array();
  var dnis = new Array();

  @if(is_array($msisdns))
    @foreach ($msisdns as $dn)
      msisdns.push("{{$dn}}");
    @endforeach
  @else
    var msisdns = "{{ $msisdns }}";
  @endif

  @if(!empty($dnis))
    @foreach ($dnis as $dni)
      dnis.push("{{$dni}}");
    @endforeach
  @endif
</script>
<script src="js/client/maindt.js?v=2.0">
</script>