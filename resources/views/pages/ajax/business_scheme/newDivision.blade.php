<form action="" class="form-horizontal" id="scheme_division" method="" name="scheme_division">
  {{ csrf_field() }}
  <div class="row justify-content-center align-items-center">
    <div class="col-12 text-center">
      <h4>
        Completa el siguiente formulario para crear una división
      </h4>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          División
        </label>
        <input autocomplete="off" class="form-control" id="inputDivision" name="inputDivision" placeholder="nombre de la division" required="" type="text"/>
      </div>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Región
        </label>
        <input autocomplete="off" class="form-control" id="inputRegion" name="inputRegion" placeholder="nombre de la region" required="" type="text"/>
      </div>
    </div>
    <div class="px-md-4 col-md-6 col-12">
      <div class="form-group">
        <label class="control-label">
          Coordinación
        </label>
        <input autocomplete="off" class="form-control" id="inputCoordinacion" name="inputCoordinacion" placeholder="nombre de la coordinacion" required="" type="text"/>
      </div>
    </div>
    <div class="col-12 text-center">
      <button class="btn btn-success createScheme" data-scheme="D" type="button">
        <i class="fa fa-floppy-o">
        </i>
        Crear división
      </button>
    </div>
  </div>
</form>
<script src="js/users/newScheme.js">
</script>
