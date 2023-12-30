<form action="" class="form-horizontal" id="scheme_coordinacion" method="" name="scheme_coordinacion">
  {{ csrf_field() }}
  <div class="row justify-content-center align-items-center">
    <div class="col-12 text-center">
      <h4>
        Completa el siguiente formulario para crear una coordinación
      </h4>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Región
        </label>
        <select class="form-control" id="SelectRegion" name="SelectRegion">
          <option value="">Seleccione una región</option>
        </select>
      </div>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Coordinación
        </label>
        <input autocomplete="off" class="form-control" id="inputCoordinacion" name="inputCoordinacion" valid="false" placeholder="nombre de la coordinacion" type="text"/>
      </div>
    </div>
    <div class="col-12 text-center">
      <button class="btn btn-success createScheme" data-scheme="C" type="button">
        <i class="fa fa-floppy-o">
        </i>
        Crear coordinación
      </button>
    </div>
  </div>
</form>
<script>
  $(regiList).each(function(){
    $('#SelectRegion').append('<option value="'+this.code+'">'+this.description+'</option>');
  });
</script>
<script src="js/users/newScheme.js">
</script>