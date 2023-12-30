function initSelectizeProfileS() {
$('#profileS').selectize();
}
function initSelectizeUserTypeS() {
$('#userTypeS').selectize();
}

initSelectizeProfileS();
initSelectizeUserTypeS();


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

});



//Inizializar inputs
function inizializeInputs(){
  let iptCheck = $('#perfil input[type=checkbox]');

  for(i=0; i < iptCheck.length; i++){
    let element = $("#"+iptCheck[i].id);
    
    $( element ).on('click',function(e){
      if($(this).prop('checked')){
        if($(this).data('val')=='N' || $(this).data('val')==undefined){
          $(this).prop('indeterminate','true');
          $(this).data('val','D');
          $(this).val('D');
        }else{
          $(this).data('val','A');
          $(this).val('A');
        }
      }else{
        if($(this).data('val')=='D'){
          $(this).prop('checked','true');
          $(this).data('val','A');
          $(this).val('A');
        }
        else{
          $(this).data('val','N');
          $(this).val('N');
        }        
      }
    })

    element.val('N');
  }
}





