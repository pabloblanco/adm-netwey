<form action="" class="form-horizontal" id="scheme_region" method="" name="scheme_region">
  {{ csrf_field() }}
  <div class="row justify-content-center align-items-center">
    <div class="col-12 text-center">
      <h4>
        Completa el siguiente formulario para crear una región
      </h4>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          División
        </label>
        <select class="form-control" id="SelectDivision" name="SelectDivision">
          <option value="">
            Seleccione una división
          </option>
        </select>
      </div>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Región
        </label>
        <input autocomplete="off" class="form-control" id="inputRegion" name="inputRegion" placeholder="nombre de la region" type="text"/>
      </div>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Coordinación
        </label>
        <input autocomplete="off" class="form-control" id="inputCoordinacion" name="inputCoordinacion" placeholder="nombre de la coordinacion" type="text"/>
      </div>
    </div>
    <div class="col-12 text-center">
      <button class="btn btn-success createScheme" data-scheme="R" type="button">
        <i class="fa fa-floppy-o">
        </i>
        Crear región
      </button>
    </div>
  </div>
</form>
<script>
  $(divList).each(function(){
    $('#SelectDivision').append('<option value="'+this.code+'">'+this.description+'</option>');
  });
</script>
<script src="js/users/newScheme.js">
</script>