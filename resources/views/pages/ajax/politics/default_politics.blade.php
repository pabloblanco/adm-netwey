<link rel="stylesheet" href="{{ asset('css/select2.min.css') }}">
<style>
  .level-2 {
    padding-left: 1.4em;
  }
  .select2 {
    width: 100% !important;
  }
  
</style>
@php
  $actions = [];
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'USR-LPP' && $policy->value > 0) {
      array_push($actions, 'select');
    } else if ($policy->code == 'USR-UPP' && $policy->value > 0) {
      array_push($actions, 'update');
    }
  }
@endphp

<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">Políticas predeterminadas</h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
      <ol class="breadcrumb">
        <li><a href="/islim/">Dashboard</a></li>
        <li class="active">Politicas predeterminadas</li>
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
                  <select id="profileS" name="profileS" class="form-control">
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

          @if (in_array('select', $actions))
            <div class="row"> 
              <div class="col-12 d-flex justify-content-center">
                <div class="row mb-5">
                  <button type="button" id="get-policy" class="btn btn-success btn-lg">
                    Buscar
                  </button>
                </div>
              </div>             
            </div>
          @endif

        </div>
      </form>
    </div>
  </div>

  <div class="row white-box mt-5 mb-5" id="showPolitics">
    <div >
      @include("pages.ajax.politics.partials.assign_politics")
    <div>

    <div class="row d-flex justify-content-center">
      <div class="col-4 text-center">
      @if (in_array('update', $actions))
        <button class="btn btn-success" id="btn_updateData">
          Guardar
        </button>
      @endif
      </div>
    </div>
  </div>

  
</div>




<script src="js/select2.min.js"></script>
<script src="js/common-modals.js"></script>
<script src="js/politics/politics-main.js"></script>

<script type="text/javascript" defer>
  //No permitir letras in los input tipo test
  jQuery("#perfil input[type=text]").on('input', function (evt) {
		jQuery(this).val(jQuery(this).val().replace(/[^0-9]/g, ''));
	});

  const actions = @php echo json_encode($actions) @endphp;
  $(document).ready(function () {
    $("#showPolitics").hide();


    //Guarda/actualiza las politicas seleccionadas
    $("#btn_updateData").click(function(){
      $(".preloader").fadeIn();
      $iptCheck = $('#perfil input[type=checkbox]:checked').serializeArray();
      $iptText = $('#perfil input[type=text]').serializeArray();

      $.ajax({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/politics/update-politics-profile',
        type: 'POST',
        data:{
          iptCheck: $iptCheck,
          iptText: $iptText,
          profile: $("#profileS").val()
        },
        success: function (res) {
          if(res.success){
            swal(res.msg , { icon: 'success' })
          }else{
            swal(res.msg , { icon: 'error' })
          }
          $(".preloader").fadeOut();
        },
        error: function (res) {
          swal(res.msg , { icon: 'error' })
          $(".preloader").fadeOut();
        }
      });

    });


    //Buscar politicas
    $("#get-policy").click(function(){     
      $("#showPolitics input[type=text]").val("0"); 
      if( $('#profileS').val() != "" ){
        $("#showPolitics").show();
        $(".preloader").fadeIn();
        get_profile( $('#profileS').val() );
        $(".preloader").fadeOut();
      }else{
        swal('Antes de continuar debe seleccionar un perfil' , { icon: 'error' })
      }
    });



  });

  
</script>
