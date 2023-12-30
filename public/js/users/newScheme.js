$(document).ready(function() {
  $('.createScheme').on('click', function(e) {

    //Se valida que no haya una con el mismo nombre.//
    $(coordList).each(function(){
      if(this.description === $('#inputCoordinacion').val()){
          $('#inputCoordinacion').attr('valid', 'false');
          return false;
      }else{
        $('#inputCoordinacion').attr('valid', 'true');
      }
    });
    $(regiList).each(function(){
      if(this.description === $('#inputRegion').val()){
          $('#inputRegion').attr('valid', 'false');
          return false;
      }else{
        $('#inputRegion').attr('valid', 'true');
      }
    });
    $(divList).each(function(){
      if(this.description === $('#inputDivision').val()){
          $('#inputDivision').attr('valid', 'false');
          return false;
      }else{
        $('#inputDivision').attr('valid', 'true');
      }
    });
    //---------------------------------------------------//

    let typeFrom = $(e.currentTarget).data('scheme');
    valid = false;
    type = "";
    // var params = new FormData();
    if (typeFrom == 'D') {
      type = 'Division';
      valid = $('#scheme_division').valid();
      //Se Valida si posee Caracteres Especiales o Tildes//
      var divi = checkSpecialChar($('#inputDivision').val());
      var regi = checkSpecialChar($('#inputRegion').val());
      var coord = checkSpecialChar($('#inputCoordinacion').val());
      if(divi == true){
        alert('No se admiten Simbolos Especiales (División).');
        return;
      }
      if(regi === true){
        alert('No se admiten Simbolos Especiales (Región).');
        return;
      }
      if(coord === true){
        alert('No se admiten Simbolos Especiales (Coordinación).');
        return;
      }
      //Se verifican las validaciones Previas//
      if($('#inputDivision').attr('valid') == 'true'){
          if($('#inputRegion').attr('valid') == 'true'){
              if($('#inputCoordinacion').attr('valid') == 'true'){
                data_ = [{
                  typeFrom: typeFrom,
                  inputDivision: divi,
                  inputRegion: regi,
                  inputCoordinacion: coord,
                }];
              }else{
                alert('Coordinación ya Existe, Por favor Coloque un Nombre distinto.');
                return;
              }
          }else{
            alert('Región ya Existe, Por favor Coloque un Nombre distinto.');
            return;
          }
      }else{
        alert('División ya Existe, Por favor Coloque un Nombre distinto.');
        return;
      }
      //params.append('typeFrom', typeFrom);
      // params.append('inputDivision', getSelectObject('inputDivision').getValue());
      //params.append('inputRegion', getSelectObject('inputRegion').getValue());
      //params.append('inputCoordinacion', getSelectObject('inputCoordinacion').getValue());
    } else {
      if (typeFrom == 'R') {
        type = 'Region';
        valid = $('#scheme_region').valid();
        //Se Valida si posee Caracteres Especiales o Tildes//
        var regi = checkSpecialChar($('#inputRegion').val());
        var coord = checkSpecialChar($('#inputCoordinacion').val());

        if(regi === true){
          alert('No se admiten Simbolos Especiales (Región).');
          return;
        }
        if(coord === true){
          alert('No se admiten Simbolos Especiales (Coordinación).');
          return;
        }
        //Se verifican las validaciones Previas//
        if($('#inputRegion').attr('valid') == 'true'){
          if($('#inputCoordinacion').attr('valid') == 'true'){
            data_ = [{
              typeFrom: typeFrom,
              idDivision: $('#SelectDivision').val(),
              inputRegion: regi,
              inputCoordinacion: coord,
            }];
          }else{
            alert('Coordinación ya Existe, Por favor Coloque un Nombre distinto.');
            return;
          }
        }else{
          alert('Región ya Existe, Por favor Coloque un Nombre distinto.');
          return;
        }
      } else {
        if (typeFrom == 'C') {
          type = 'Coordinación';
          valid = $('#scheme_coordinacion').valid();
          //Se Valida si posee Caracteres Especiales o Tildes//
          var coord = checkSpecialChar($('#inputCoordinacion').val());
          if(coord === true){
            alert('No se admiten Simbolos Especiales (Coordinación).');
            return;
          }
          //Se verifican las validaciones Previas//
          if($('#inputCoordinacion').attr('valid') == 'true'){
            data_ = [{
              typeFrom: typeFrom,
              idRegion: $('#SelectRegion').val(),
              inputCoordinacion: coord,
            }];

          }else{
            alert('Coordinación ya Existe, Por favor Coloque un Nombre distinto.');
            return;
          }
        }
      }
    }
    if (valid && data_.length > 0) {
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/user/scheme/create',
        type: 'POST',
        dataType: 'json',
        data: data_[0],
        error: function() {
          swal({
            title: "Hubo un problema en crear la " + type,
            text: null,
            icon: "warning",
          });
        },
        success: function(res) {
          if (res.success) {
            swal({
              title: "Creacion exitosa de la " + type,
              text: null,
              icon: "success",
            });
            getview('user/scheme');
            search_listScheme();
          }
          $('#myModal .close').trigger('click');
        }
      });
    }
  });
});
function checkSpecialChar(data){
  var symbols = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789"; 
  var accent = "ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ";
  var accentCorrect = "AAAAAAACEEEEIIIIDNOOOOOOUUUUYBSaaaaaaaceeeeiiiidnoooooouuuyybyRr";
  for (var i = 0; i < data.length; i++){     
    if (symbols.indexOf(data.charAt(i)) == -1 && accent.indexOf(data.charAt(i)) == -1){    
      return true;
    } 
  }
  for (var i = 0; i < data.length; i++){   
    var charPosition = accent.indexOf(data.charAt(i));  
    if (charPosition != -1){   
      data = data.split(''); 
      data[i] = accentCorrect[charPosition];
      data = data.join('');
    } 
  }
  return data;
}

$(function() {
  jQuery.extend(jQuery.validator.messages, {
    required: "Este campo es obligatorio.",
    remote: "Por favor, rellena este campo.",
    email: "Por favor, escribe una dirección de correo válida",
    url: "Por favor, escribe una URL válida.",
    date: "Por favor, escribe una fecha válida.",
    dateISO: "Por favor, escribe una fecha (ISO) válida.",
    number: "Por favor, escribe un número entero válido.",
    digits: "Por favor, escribe sólo dígitos.",
    creditcard: "Por favor, escribe un número de tarjeta válido.",
    equalTo: "Por favor, escribe el mismo valor de nuevo.",
    accept: "Por favor, escribe un valor con una extensión aceptada.",
    maxlength: jQuery.validator.format("Por favor, no escribas más de {0} caracteres."),
    minlength: jQuery.validator.format("Por favor, no escribas menos de {0} caracteres."),
    rangelength: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1} caracteres."),
    range: jQuery.validator.format("Por favor, escribe un valor entre {0} y {1}."),
    max: jQuery.validator.format("Por favor, escribe un valor menor o igual a {0}."),
    min: jQuery.validator.format("Por favor, escribe un valor mayor o igual a {0}.")
  });
  $("#scheme_division").validate({
    rules: {
      inputDivision: {
        required: true,
        minlength: 3
      },
      inputRegion: {
        required: true,
        minlength: 3
      },
      inputCoordinacion: {
        required: true,
        minlength: 3
      }
    }
  });
  $("#scheme_region").validate({
    rules: {
      SelectDivision: {
        required: true
      },
      inputRegion: {
        required: true,
        minlength: 3
      },
      inputCoordinacion: {
        required: true,
        minlength: 3
      }
    }
  });
  $("#scheme_coordinacion").validate({
    rules: {
      SelectRegion: {
        required: true
      },
      inputCoordinacion: {
        required: true,
        minlength: 3
      }
    }
  });
});