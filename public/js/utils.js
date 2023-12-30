//configurando idioma para el calendario
$(function() {
  if ($.fn.datepicker && $.fn.datepicker.dates) {
    $.fn.datepicker.dates['es'] = {
      days: ["Domingo", "Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado"],
      daysShort: ["Dom", "Lun", "Mar", "Mie", "Jue", "Vie", "Sab"],
      daysMin: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
      months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
      monthsShort: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dec"],
      today: "Hoy",
      clear: "Borrar",
      format: "dd/mm/yyyy",
      titleFormat: "MM yyyy",
      /* Leverages same syntax as 'format' */
      weekStart: 0
    };
  }
});
var currentModuleAPI = '';
var currentModuleViewAPI = '';

function sav(form_id, onSuccess, onError) {
  $(".preloader").fadeIn();
  var frm = $(form_id),
    ban = false;
  frm.submit(function(e) {
    e.preventDefault();
    var params = frm.serialize();
    if (!ban) {
      ban = true;
      request(frm.attr('action'), frm.attr('method'), params, function(res) {
        $('#myModal').hide();
        $('.modal-backdrop').remove();
        $("body").removeClass("modal-open");
        $(".preloader").fadeOut();
        //console.log('sav(success)', res);
        onSuccess(res);
      }, function(res) {
        $(".preloader").fadeOut();
        //console.log('sav(error)', res);
        onError(res);
      });
    }
  });
}

function trimestralchar() {
  request('dashboard/P/quarterly', 'GET', null, function(res) {
    $('#upsG').empty();
    printbar(res, 'ups');
  }, function(res) {
    alert(res.msg);
  });
  request('dashboard/R/quarterly', 'GET', null, function(res) {
    $('#recharG').empty();
    printbar(res, 'recharger');
  }, function(res) {
    alert(res.msg);
  });
}

function del(req, item_name, onSuccess, onError, data = null, msg = null) {
//  console.log(req);
  var ban = false;
  var sms = "";
  if (msg == null) sms = item_name ? '¿desea eliminar el registro: '.concat(item_name).concat('?') : '¿Desea eliminar los registros?';
  else sms = msg;
  if (confirm(sms)) {
    $(".preloader").fadeIn();
    if (!ban) {
      ban = true;
      request(req, 'DELETE', data, function(res) {
        $(".preloader").fadeOut();
        onSuccess(res);
      }, function(res) {
        $(".preloader").fadeOut();
        console.log('del(error)', res);
        onError(res);
      });
    }
  }
}

function getViewFromForm(frm, container, onSuccess, onError) {
  var ban = false;
  $(".preloader").fadeIn();
  frm.submit(function(e) {
    e.preventDefault();
    var params = frm.serialize();
    if (!ban) {
      ban = true;
      request(frm.attr('action'), frm.attr('method'), params, function(res) {
        $(".preloader").fadeOut();
        onSuccess(res);
      }, function(res) {
        $(".preloader").fadeOut();
        console.log('sav(error)', res);
        onError(res);
      });
    }
  });
}

function getSelectObject(id) {
  return $('#'.concat(id)).eq(0).data('selectize');
}

function setSelect(id, val) {
  var selectize = getSelectObject(id);
  if (selectize != null)
    if (selectize != undefined) selectize.setValue(val, false);
}
jQuery.validator.addMethod("notEqualTo", function(value, element, param) {
  var notEqual = true;
  value = $.trim(value);
  for (i = 0; i < param.length; i++) {
    if (value == $.trim($(param[i]).val())) {
      notEqual = false;
    }
  }
  return this.optional(element) || notEqual;
}, "Seleccione un valor diferente");
$.validator.addMethod("valueNotEquals", function(value, element, arg) {
  return arg !== value;
}, "Seleccione un tipo de usuario");
var gMapsLoaded = false;
window.gMapsCallback = function() {
  gMapsLoaded = true;
  $(window).trigger('gMapsLoaded');
}
window.loadGoogleMaps = function() {
  if (gMapsLoaded) return window.gMapsCallback();
  var script_tag = document.createElement('script');
  script_tag.setAttribute("type", "text/javascript");
  //script_tag.setAttribute("src","http://maps.google.com/maps/api/js?key=AIzaSyAV7fdyjOww72touSuCpNIS8yhYiUQfOXk&sensor=false&callback=gMapsCallback&libraries=places");
  script_tag.setAttribute("src", "//maps.google.com/maps/api/js?key=AIzaSyDtofLeDzr2v28KbwdmB1FtN2hkB8q0gZI&sensor=false&callback=gMapsCallback&libraries=places");
  (document.getElementsByTagName("head")[0] || document.documentElement).appendChild(script_tag);
}

function getDateDiff(a, b) {
  /*a = new Date(a);

  b = new Date(b);*/
  return Math.round((b.getTime() - a.getTime()) / (1000 * 60 * 60 * 24));
}

function sumDays(date, nd) {
  nd = Math.round(nd * (1000 * 60 * 60 * 24));
  return new Date(date.getTime() + nd);
}
//Funcion que valida que el archivo sea un csv
function validatFiles(id, errorId, error, error2) {
  $('#'.concat(errorId)).html('');
  if ($('#'.concat(id))[0].files.length == 0) {
    $('#'.concat(errorId)).html(error);
    return false;
  } else {
    if (!((document.getElementById(id).files[0].type == 'application/vnd.ms-excel') || (document.getElementById(id).files[0].type == 'text/csv'))) {
      $('#'.concat(errorId)).html(error2);
      return false;
    }
  }
  return true;
}
//END Funcion que valida que el archivo sea un csv