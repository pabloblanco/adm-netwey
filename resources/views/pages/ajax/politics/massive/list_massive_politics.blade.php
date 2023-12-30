<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
  .level-2 {
    padding-left: 1.4em;
  }
  .select2 {
    width: 100% !important;
  }
  .size25{
    font-size: 20px !important;
  }

  .check-lg{
    height: 17px;
    width: 17px;
  }
</style>
@php
  $actions = []; 
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'USR-LAP' && $policy->value > 0) {
      array_push($actions, 'select');
    } else if ($policy->code == 'USR-EAP' && $policy->value > 0) {
      array_push($actions, 'update');
    }
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Asignación masiva de políticas a usuarios</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Asignación masiva de políticas a usuarios</li>
      </ol>
    </div>
  </div>
</div>

<div class="container">

  <div class="row">
    <div class="col-md-12">
      <form>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Tipo Usuario</label>
                        <select id="userTypeS" name="userTypeS" class="form-control">
                            <option value="">Seleccione un tipo de usuario</option>
                            @if(session('user.platform')=='admin')
                            <option value="admin">Admin</option>
                            @endif
                            @if(session('user.platform')=='admin'||session('user.platform')=='coordinador')
                            <option value="coordinador">Coordinador</option>
                            @endif
                            <option value="vendor">Vendedor</option>
                            <option value="call">Callcenter</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Perfiles</label>
                        <select id="profileS" name="profileS" class="form-control" >
                            <option value="">Selecciona</option>
                            @if(!empty($profiles))
                            @foreach($profiles as $profile)
                                <option value="{{$profile->id}}">{{$profile->name}}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                </div>

            </div>

            <div class="row">
              <div class="col-md-6">
                    <div class="form-group">
                        <label class="control-label">Usuario</label>
                        <select id="user" name="user" class="form-control" multiple>
                        <option value="">Seleccione un Usuario</option>
                        </select>
                        <label class="control-label" id="error_seller"></label>
                    </div>
              </div>

              <div class="col-md-6 d-flex align-items-center justify-content-center">
                @if (in_array('select', $actions))
                <div class="row"> 
                  <div class="col-12 d-flex justify-content-center">
                    <div class="row mb-5">
                      <button type="button" id="show-policy" class="btn btn-success btn-lg">
                        Continuar
                      </button>
                    </div>
                  </div>             
                </div>
              @endif
              </div>

            </div>


        </div>
      </form>
    </div>
  </div>

  <div class="row mt-5 mb-5" id="showPolitics">


      <div class="col-12 px-5 white-box mb-5">
        
        <div class="form-group">
            <p class="text-center size25">
              Significado de las casillas de verificación
            </p>
        </div>

        <div class="row px-5">

          <div class="col-12 col-sm-4 form-group d-flex align-items-center">
            <input type="checkbox" class="check-lg" onclick="return false;">
            <label class="mt-3 mx-2">
              No realiza ninguna acción
            </label>
          </div>

          <div class="col-12 col-sm-4 form-group d-flex align-items-center">
            <input type="checkbox" class="check-lg" id="checkInitial" onclick="return false;">
            <label class="mt-3 mx-2">
              Eliminar política
            </label>
          </div>

          <div class="col-12 col-sm-4 form-group d-flex align-items-center">
            <input type="checkbox" class="check-lg" checked="true" onclick="return false;">
            <label class="mt-3 mx-2">
              Agregar política
            </label>
          </div>

        </div>

      </div>
    
    <div id="inputPolitics" class="white-box">
      
      <div class="">
        <p class="text-center size25">
          Gestión de políticas
        </p>
      </div>
    
      <div >
        @include("pages.ajax.politics.partials.assign_politics")
      </div>

      <div class="row">
        <div class="col-12 d-flex justify-content-center">
          @if (in_array('update', $actions))
            <button class="btn btn-success" id="btn_updateData">
              Guardar
            </button>
          @endif
        </div>
      </div>

    </div>
  </div>

  
</div>




<script src="js/select2.min.js"></script>
<script src="js/common-modals.js"></script>
<script src="js/politics/massive-politics.js"></script>

<script type="text/javascript" defer>
  $("#showPolitics input[type=text]").val("0"); 
  $("#checkInitial").data('checked',1).prop('indeterminate',true);

  //No permitir letras en los input tipo test
  jQuery("#perfil input[type=text]").on('input', function (evt) {
		jQuery(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
	});


  $(document).ready(function () {
    $("#showPolitics").hide();

    //Buscar perfiles por tipo de usuario
    $("#get-policy").click(function(){
      $(".preloader").fadeIn();
      get_profile( $('#profileS').val() );
      $(".preloader").fadeOut();
    });

    inizializeInputs();
  });


  $("#profileS").change(function(){
    $("#user")[0].selectize.clearOptions();
  })
  

  //Select de usuarios
  $('#user').selectize({
      valueField: 'email',
      labelField: 'username',
      searchField: ["username", "email"],

      options: [],
      create: false,
      persist: false,
      render: {
          option: function(item, escape) {

              opt = "<div>";
              opt += '<span>' + item.username.toLocaleUpperCase() + "</span>";
              opt += '<span class="aai_description mb-0" style="color:#666; opacity:0.75; font-weight:400;">' + item.email +"</span>";
              opt += '<ul class="aai_meta my-0">';
              opt += '<li style="opacity:0.5"><strong>' + item.profile + "</strong></li>";
              opt += "</ul>";

              return opt;
          }
      },
      load: function(query, callback) {
          profile = $("#profileS").val();

          if (!query.length){
              return callback();
          }

          if ( !profile ){
            swal("Debe seleccionar un perfil" , { icon: 'error' });
            return callback();
          }

          $.ajax({
              headers: {
                  'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
              },
              url: 'api/politics/get-users-by-profile',
              type: 'POST',
              dataType: 'json',
              cache: false,
              data: {
                  name: query,
                  profile: profile
              },
              error: function(res) {
                  callback();
              },
              success: function(res) {
                console.log(res)
                  if (res.success)
                      callback(res.users);
                  else{
                      swal(res.msg , { icon: 'error' })
                      callback();
                  }
              }
          });
      }
  });


  //Boton continuar
  $("#show-policy").click(function(){
    $(".preloader").fadeIn();

    let sltProfile = $("#profileS");
    let divShowPolitics = $("#showPolitics");

    if( sltProfile.val() )
      divShowPolitics.show();
    else
      swal("Debe seleccionar un perfil" , { icon: 'error' })
    
    $(".preloader").fadeOut();
  });


  //Boton de Guardar
  $("#btn_updateData").click( function(){
    let sltProfile = $("#profileS");

    if( !sltProfile.val() ){
       swal("Debe seleccionar un perfil" , { icon: 'error' })
    }else
      if( !$("#user").val() ){
        swal({
          title: "Importante",
          text: "No ha seleccionado ningún usuario específico, los cambios se aplicarán a todos los usuarios con el Perfil: "+ sltProfile.text()+ " ¿Desea continuar?",
          icon: "warning",
          buttons: ["Cancelar", "Continuar"],
          dangerMode: false,
        })
        .then(( confirm ) => {
          if (confirm) {
            updatePolicies();
          }
        });
      }else{
        updatePolicies();
      }
  });

    

  function updatePolicies(){
    $(".preloader").fadeIn();

    let iptCheck = $('#perfil input[type=checkbox]').serializeArray();
    let iptText = $('#perfil input[type=text]').serializeArray();
    let selectedUsers = $("#user").val();
    let selectprofile = $("#profileS").val();

    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/politics/edit-policy-users',
      type: 'POST',
      data:{
        iptCheck: iptCheck,
        iptText: iptText,
        users: selectedUsers,
        idProfile: selectprofile
      },
      success: function (res){
        console.log(res);
        if(res.success)
          swal(res.msg , { icon: 'success' })
        else
          swal(res.msg , { icon: 'error' })
        
        $(".preloader").fadeOut();
      },
      error: function (res) {
        console.log(res)
        swal(res.msg , { icon: 'error' })
        $(".preloader").fadeOut();
      }
    });

    $(".preloader").fadeOut();
  }
    
  

  
</script>
