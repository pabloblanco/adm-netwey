function initSelectizeProfileS() {
    $('#profileS').selectize();
  }
  function initSelectizeUserTypeS() {
    $('#userTypeS').selectize();
  }

  //Select's del Modal
  function initSelectizeTypeUser() {
    $('#userTypeModal').selectize();
  }
  function initSelectizeProfile() {
    $('#profileModal').selectize();
  }
  function initSelectizeInputTypeModal() {
    $('#typeInputModal').selectize();
  }
  //Select's del Modal

  initSelectizeProfileS();
  initSelectizeUserTypeS();

  initSelectizeTypeUser();
  initSelectizeProfile();
  initSelectizeInputTypeModal();


  $("#exampleInput").hide();


  // Cuando hay un cambio en select "Tipo usuario" se buscan los perfiles
  $('#userTypeS').on('change', async () => {
    $('.preloader').fadeIn()

    var data = {
      platform: $('#userTypeS').val()
    };
    
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/user/get/profiles-by-platform',
      type: 'post',
      data: data,
      success: function (res) {

        if (res) {
          
          valact = $('#profileS').val();

          $('#profileS')[0].selectize.destroy();
          $("#profileS [value!='']").remove();

          res.profiles.forEach(function (profile) {
            var optVal = {
              value: profile.id,
              text: profile.name,
              'data-platform': profile.platform,
              'data-hassup': profile.has_supervisor,
              'data-type': profile.type,
            }

            $('#profileS').append($('<option>', optVal));
          });
          initSelectizeProfileS();
        }
        $(".preloader").fadeOut();
      },
      error: function (res) {
        $(".preloader").fadeOut();
      }
    });

  })


  // Cuando hay un cambio en select "Tipo usuario" del MODAL
  $('#userTypeModal').on('change', async () => {
    $('.preloader').fadeIn()

    var data = {
      platform: $('#userTypeModal').val()
    };
    
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'api/user/get/profiles-by-platform',
      type: 'post',
      data: data,
      success: function (res) {

        if (res) {
          
          $('#profileModal')[0].selectize.destroy();
          $("#profileModal [value!='']").remove();

          res.profiles.forEach(function (profile) {
            var optVal = {
              value: profile.id,
              text: profile.name,
              'data-platform': profile.platform,
              'data-hassup': profile.has_supervisor,
              'data-type': profile.type,
            }

            $('#profileModal').append($('<option>', optVal));
          });
          initSelectizeProfile();
        }
        $(".preloader").fadeOut();
      },
      error: function (res) {
        $(".preloader").fadeOut();
      }
    });

  })

  //Muestra el Modal
  $('button#new-policy').on('click', function () {
    $('div#myModal').modal()
  })


  //Obtener politicas de acuerdo al tipo de perfil 
  function get_profile(type) {
    $('#perfil input[type=checkbox]').prop('checked', false);
    $('#perfil input[type=checkbox]').val('0');

    if (type !== '') {
      if (type == '1') {
        $('#perfil input[type=checkbox]').prop('checked', true);
        $('#perfil input[type=checkbox]').val('1');
      } else {

        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: 'api/user/get/profile/' + type,
          method: 'GET',
          success: function (res) {
            $.each(res, function (key, value) {
              $('#' + value.item).show();
              $('#' + value.panel).show();
              if (value.type == 'CH') {
                if (value.policy != 'value_17154') {//Politica de desbloque de equipos
                  $('#' + value.policy).prop('checked', true);
                  $('#' + value.policy).val('1');
                }
              } else {
                $('#' + value.policy).val( value.value );
              }
              
            });
          },
          error: function (res) {
            alert('Ocurrio un error al conectar con el servidor. Por favor intente mas tarde');
          }
        });

      }
    }
  }

