function getClientTable() {
  if (isValidateForm()) {
    var files = document.getElementById('msisdn_file');
    var params = new FormData();
    params.append('_token', $('meta[name="csrf-token"]').attr('content'));
    if ($('#client_manual_check').is(':checked')) {
      params.append('msisdn_select', getSelectObject('msisdn_select').getValue());
      $('#msisdn_select').data('selectize').clearOptions();
    } else if ($('#client_file_check').is(':checked')) {
      params.append('msisdn_file', files.files[0]);
    } else if ($('#client_name_manual_check').is(':checked')) {
      params.append('name_select', getSelectObject('name_select').getValue());
      $('#name_select').data('selectize').clearOptions();
    }
    $(".preloader").fadeIn();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      url: 'view/client/get',
      type: 'post',
      data: params,
      contentType: false,
      processData: false,
      cache: false,
      async: true,
      success: function(res) {
        if (res.success) $('#client_table_area').html(res.msg);
        else alert('Debes seleccionar al menos un cliente del listado.');
        $(".preloader").fadeOut();
      },
      error: function(res) {
        console.log('Error ', res);
        alert('Hubo un error');
        $(".preloader").fadeOut();
      }
    });
  } else {
    console.log('isValidateForm()', isValidateForm());
  }
}

function getAllClientTable() {
  $(".preloader").fadeIn();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    url: 'view/client/get',
    type: 'post',
    contentType: false,
    processData: false,
    cache: false,
    async: true,
    success: function(res) {
      $('#client_table_area').html(res.msg);
      $(".preloader").fadeOut();
    },
    error: function(res) {
      console.log('Error ', res);
      alert('Hubo un error');
      $(".preloader").fadeOut();
    }
  });
}

function saveProfile() {
  if ($('#edit_client_form').valid()) {
    //formatear numeros en formato internacional antes de guardar
    formatPhone($('#modal_edit_phone'));
    formatPhone($('#modal_edit_phone_2'));
    sav('#edit_client_form', function(res) {
      alert(res);
      getview('client');
    }, function(res) {
      alert('Ocurrio un error al realizar su operación');
      console.log('error ', res);
    });
  } else {
    $('#edit_client_form').submit(function(e) {
      e.preventDefault();
      alert("Verifique que los campos sean cargados correctamente");
    })
  }
}

function changecoor() {
  if ($('#change_coor_form').valid()) {
    sav('#change_coor_form', function(res) {
      alert(res.message);
      $('#modal_changecoor .close').trigger('click');
    }, function(res) {
      alert('Ocurrio un error al realizar su operación');
      console.log('error ', res);
    });
  } else {
    $('#change_coor_form').submit(function(e) {
      e.preventDefault();
    })
  }
}

function updateB28() {
  let imei = $('#imei_form').val();
  if (imei != '' && String(imei).length >= 14 && String(imei).length <= 15 && !isNaN(parseInt(imei)) && $('#editing_msisdn').val() != '') {
    $('#msisdn_imei').val($('#editing_msisdn').val());
    sav('#updateB28_form', function(res) {
      alert(res.msg);
      $('#modal_updateb28 .close').trigger('click');
    }, function(res) {
      alert('Ocurrio un error al realizar su operación, por favor vuelva a intentar');
      console.log('error ', res);
    });
  } else {
    alert('IMEI no válido, el IMEI debe ser númerico y tener 14 o 15 dígitos');
    $('#updateB28_form').submit(function(e) {
      e.preventDefault();
    });
  }
}

function doAction(action, onSuccessCB, onErrorCB) {
  $(".preloader").fadeIn();
  var URL = 'api/client/altan/'.concat(action).concat('/').concat($('#editing_msisdn').val());
  var params = new FormData();
  params.append('token', $('meta[name="csrf-token"]').attr('content'));
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      $(".preloader").fadeOut();
      //es = JSON.parse(res);
      onSuccessCB(res);
    },
    error: function(res) {
      $(".preloader").fadeOut();
      console.log('Error ', res);
      onErrorCB(res);
    }
  });
}

function myMap(lat, lng, detc_lat = null, detc_lng = null) {
  //console.log(detc_lat,detc_lng)
  var map;
  var markers = [];
  if (detc_lat != null && detc_lng != null) {
    var mapProp = {
      center: new google.maps.LatLng(lat, lng),
      zoom: 10,
    };
  } else {
    var mapProp = {
      center: new google.maps.LatLng(lat, lng),
      zoom: 13,
    };
  }
  map = new google.maps.Map(document.getElementById("maps"), mapProp);
  var marker = new google.maps.Marker({
    position: new google.maps.LatLng(lat, lng),
    map: map,
    title: 'Origen',
    icon: 'img/me.png'
  });
  markers.push(marker);
  if (detc_lat != null && detc_lng != null) {
    var marker2 = new google.maps.Marker({
      position: new google.maps.LatLng(detc_lat, detc_lng),
      map: map,
      title: 'Movilidad',
      icon: 'img/nome.png'
    });
    markers.push(marker2);
  }
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: 'api/client/getpoint/' + lat + '/' + lng,
    method: "GET",
    success: function(res) {
      // $(".preloader").fadeOut();
      res = JSON.parse(res);
      res.forEach(function(point) {
        var html = '<h4>' + point.name + '</h4>';
        if (point.address) html += '<p>' + point.address + '</p>';
        var infowindow = new google.maps.InfoWindow({
          content: html
        });
        var marker_rech = new google.maps.Marker({
          position: new google.maps.LatLng(point.lat, point.lng),
          map: map,
          title: point.name,
          icon: 'img/points.png'
        });
        markers.push(marker_rech);
        google.maps.event.addListener(marker_rech, 'click', function() {
          infowindow.open(map, marker_rech);
        });
      });
    },
    error: function(res) {
      $(".preloader").fadeOut();
      console.log('Error ', res);
    }
  });
}

function suspendedSetHistory() {
  $(".preloader").fadeIn();
  var URL = 'api/client/set-suspended-history'; //.concat($('#editing_msisdn').val());
  var params = new FormData();
  params.append('token', $('meta[name="csrf-token"]').attr('content'));
  params.append('msisdn', $('#editing_msisdn').val());
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      $(".preloader").fadeOut();
      ////es = JSON.parse(res);
      console.log('OK ', res);
      // onSuccessCB(res);
    },
    error: function(res) {
      $(".preloader").fadeOut();
      console.log('Error ', res);
      // onErrorCB(res);
    }
  });
}

function suspend() {
  try {
    if (confirm('¿Seguro que desea suspender la linea?')) {
      doAction('suspend', function(res) {
        if (res.status == 'success') {
          suspendedSetHistory();
          $('#modal_activate_btn').hide();
          $('#modal_suspend_btn').hide();
          $('#modal_status').html('Suspendida');
          $('#modal_activate_btn').show();
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de suspender');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error Catch ', e);
  }
}

function suspendTheft() {
  try {
    if (confirm('¿Seguro que desea suspender la linea por robo o extravío?')) {
      doAction('suspendtheft', function(res) {
        if (res.status == 'success') {
          $('#modal_suspend_btn').hide();
          $('#modal_suspend_theft_btn').hide();
          $('#modal_status').html('Suspendido por robo o extravío');
          $('#modal_activate_btn').show();
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de suspender el dn');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error Catch ', e);
  }
}

function activate() {
  try {
    if (confirm('¿Seguro que desea activar la linea?')) {
      doAction('activate', function(res) {
        if (res.status == 'success') {
          $('#modal_activate_btn').hide();
          $('#modal_status').html('Preactivo');
          $('#modal_suspend_btn').show();
          $('#modal_suspend_theft_btn').show();
          $('#btn-active-chcoor').show();
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de activar');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function preDesactivate() {
  try {
    if (confirm('¿Seguro que desea pre-desactivar la linea?')) {
      doAction('pre-desactivate', function(res) {
        if (res.status == 'success') {
          refreshData(false);
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de activar');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function ractivate() {
  try {
    if (confirm('¿Seguro que desea re-activar la linea?')) {
      doAction('reactivate', function(res) {
        if (res.status == 'success') {
          $('#btn-chcoor-modal').show();
          refreshData(false);
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de activar');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function barring() {
  try {
    if (confirm('¿Seguro que desea desactivar trafico saliente de la linea?')) {
      doAction('barring', function(res) {
        if (res.status == 'success') {
          refreshData(false);
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de desactivar la linea');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function unbarring() {
  try {
    if (confirm('¿Seguro que desea activar trafico saliente de la linea?')) {
      doAction('unbarring', function(res) {
        if (res.status == 'success') {
          $('#btn-chcoor-modal').show();
          refreshData(false);
        } else if (res.status == 'error') {
          alert(res.message);
        }
      }, function(res) {
        alert('Hubo un error tratando de activar la linea');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch', e);
  }
}

function refreshData(opendModal, data = null) {
  if (data) {
    if (data.dn_type != 'T') {
      $('#tabportabilidad').hide();
    } else {
      $('#tabportabilidad').show();
    }
    if (data.dn_type == 'F') {
      // console.log(data);
      $("#retention-tab").hide();
      $('#consumos-tab').hide();
      $('#salud-tab').hide();
      $('#compensaciones-tab').hide();
      $('#blim-tab').hide();
      $('#modal_activate_btn').hide();
      $('#modal_suspend_btn').hide();
      $('#modal_suspend_theft_btn').hide();
      $('#btn-chcoor-modal').hide();
      $('#btn-active-chcoor').hide();
      $('#btn-pred').hide();
      $('#btn-react').hide();
      $('#btn-reduce-content').hide();
      $('#modal_suspend_par_btn').hide();
      $('#modal_activate_par_btn').hide();
      $('#content-barring').hide();
      $('#modal_b28').hide();
      $('#coord-content').hide();
      $('#coordenadas-tab').hide();
      $('#suspension-tab').hide();
      $('#promocion-tab').hide();
      $('#modal_status').html(data.status);
      if (data.lat != 'N/A' && data.lng != 'N/A') {
        myMap(data.lat, data.lng);
        $('#noMap').hide();
        $('#maps').css('filter', 'none');

      }else{
        myMap(19.430832226515257, -99.11752864477798);
        $('#noMap').show();
        $('#maps').css('filter', 'blur(5px)');
      }
      if (opendModal) {
        $('#detail').modal({
          keyboard: false,
          backdrop: 'static'
        });
      }
      $(".preloader").fadeOut();
      return;
    }
  }
  try {
    doAction('profile-new', function(res) {
      if (res.status == 'success') {
        $.ajax({
          headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: "api/services/getServRetByPeriod",
          method: "POST",
          data: {
            'days': res.msisdn.period_serv_days
          },
          dataType: "json",
          success: function(response) {
            if (response.success == true) {
              $.each(response.ret_services, function(i, item) {
                $('#retservice').append($('<option>', {
                  value: item.id,
                  text: item.title,
                  'data-days': item.days
                }));
              });
            } else {
              $("#retention-tab").addClass('d-none');
              console.log(response.msg);
            }
          },
          error: function(err) {
            $("#retention-tab").addClass('d-none');
            console.log('Error ', err);
          }
        });
      }
      if (res.status == 'success' && res.msisdn.isPreDesactivate) {
        $('#modal_activate_btn').hide();
        $('#modal_suspend_btn').hide();
        $('#modal_suspend_theft_btn').hide();
        $('#btn-chcoor-modal').hide();
        $('#btn-active-chcoor').hide();
        $('#btn-pred').hide();
        $('#btn-react').show();
        if (opendModal) {
          //$('#open_detail_btn').click();
          $('#detail').modal({
            keyboard: false,
            backdrop: 'static'
          });
        }
      } else {
        if (res.status == 'success') {
          let btnActivate = $('#modal_activate_btn'),
            btnSuspend = $('#modal_suspend_btn'),
            btnReact = $('#btn-react').show(),
            btnPred = $('#btn-pred').hide(),
            btnCoo = $('#btn-chcoor-modal'),
            btnActCoo = $('#btn-active-chcoor'),
            btnSusPar = $('#modal_suspend_par_btn'),
            btnReactPar = $('#modal_activate_par_btn');
          btnSuspTheft = $('#modal_suspend_theft_btn');
          btnActivate.hide();
          btnSuspend.hide();
          btnSuspTheft.hide();
          btnReact.hide();
          btnPred.show();
          btnActCoo.hide();
          btnReactPar.hide();
          btnSusPar.show();
          if (res.msisdn.type == 'H' || res.msisdn.type == 'M' || res.msisdn.type == 'MH') {
            $('#content-barring').hide();
            $('#modal_b28').hide();
            if (res.msisdn.type != 'M' && res.msisdn.type != 'MH') {
              $('#coord-content').show();
              if (res.msisdn.activeBuyChangeCoord) {
                btnActCoo.show();
                btnCoo.hide();
              } else {
                btnCoo.show();
                btnActCoo.hide();
              }
            }
            var date_expire = 'N/A';
            if (res.msisdn.status != 'expired' && res.msisdn.expired_date) {
              date_expire = res.msisdn.expired_date.split(" ")[0];
            }
            $('#modal_date_expire').html(date_expire);
            $('#modal_gb').html(res.msisdn.remaining_gb.toFixed(2) + " GB");
          } else {
            $('#coord-content').hide();
            $('#content-barring').show();
            if (res.msisdn.offers_detail && res.msisdn.offers_detail.length) {
              let html = '';
              let datos_t = 0;
              let sms_t = 0;
              let minutos_t = 0;
              let datos_r = 0;
              let sms_r = 0;
              let minutos_r = 0;
              let datos_e = '';
              let sms_e = '';
              let minutos_e = '';
              const regex = /-/gi;
              res.msisdn.offers_detail.forEach(function(ele) {
                if (ele.name.toLowerCase().indexOf('datos') !== -1) {
                  datos_t += ele.total;
                  datos_r += ele.remaing;
                  let exp = (ele.expired.split(" ")[0]);
                  if (exp.replace(regex, '') > datos_e.replace(regex, '')) {
                    datos_e = exp;
                  }
                }
                if (ele.name.toLowerCase().indexOf('sms') !== -1) {
                  sms_t += parseInt(ele.total);
                  sms_r += parseInt(ele.remaing);
                  let exp = (ele.expired.split(" ")[0]);
                  if (exp.replace(regex, '') > sms_e.replace(regex, '')) {
                    sms_e = exp;
                  }
                }
                if (ele.name.toLowerCase().indexOf('minutos') !== -1) {
                  minutos_t += parseInt(ele.total);
                  minutos_r += parseInt(ele.remaing);
                  let exp = (ele.expired.split(" ")[0]);
                  if (exp.replace(regex, '') > minutos_e.replace(regex, '')) {
                    minutos_e = exp;
                  }
                }
                // html += '<div class="col-md-4">'+
                //         '<label class="col-md-12">'+
                //         '<strong>' + ele.name + '</strong>' +
                //         '</label>'+
                //         '<label class="col-md-12">'+
                //         'Expira: <strong>'+
                //         ele.expired.split(" ")[0]+
                //         '</strong>'+
                //         '</label>'+
                //         '<label class="col-md-12">'+
                //         'Restante: <strong>'+
                //         ele.remaing+ (ele.name.indexOf('datos')>-1? 'GB':'')+
                //         '</strong>'+
                //         '</label>'+
                //         '</div>';
              });
              // if($('#modal_msisdn').text() == '5612134998'){
              if (datos_t > 0) {
                porc = parseFloat(Math.round(datos_r * 100) / datos_t).toFixed(0);
                if (porc > 100) {
                  porc = 100;
                }
                datos_r = datos_r.toFixed(2)
                html += '<div class="col-12 col-md-4 pb-4 text-center">' + '<p class="mb-0 px-3 pb-1 pt-0 title-details">Datos</p>' + '<p class="mb-0 px-3 py-0" style="font-weight: 500;">GB disponibles</p>' + '<p class="mb-0 px-3 py-0 title-details">' + datos_r + ' GB</p>' +
                  // '<div class="col-10 col-sm-6 item-cont-details mt-auto mx-auto py-0">'+
                  //     '<div class="group-progress">'+
                  //         '<div class="progress mt-2 mb-3" style="height: 12px;border-radius: 30px;background: #DDD;">'+
                  //             '<div class="progress-bar progress-bar-success back-c-p" role="progressbar" style="border-radius:30px; width: '+porc+'%" aria-valuenow="'+porc+'" aria-valuemin="0" aria-valuemax="100"></div>'+
                  //         '</div>'+
                  //     '</div>'+
                  // '</div>'+
                  '<p class="mb-0 px-3 py-0" style="font-weight: 500;">Expira: <strong>' + datos_e + '</strong></p>' + '</div>';
              }
              if (minutos_t > 0) {
                porc = parseFloat(Math.round(minutos_r * 100) / minutos_t).toFixed(0);
                if (porc > 100) {
                  porc = 100;
                }
                html += '<div class="col-12 col-md-4 pb-4 text-center">' + '<p class="mb-0 px-3 pb-1 pt-0 title-details">Minutos</p>' + '<p class="mb-0 px-3 py-0" style="font-weight: 500;">Minutos disponibles</p>' + '<p class="mb-0 px-3 py-0 title-details">' + minutos_r + '</p>' +
                  // '<div class="col-10 col-sm-6 item-cont-details mt-auto mx-auto py-0">'+
                  //     '<div class="group-progress">'+
                  //         '<div class="progress mt-2 mb-3" style="height: 12px;border-radius: 30px;background: #DDD;">'+
                  //             '<div class="progress-bar progress-bar-success back-c-p" role="progressbar" style="border-radius:30px; width: '+porc+'%" aria-valuenow="'+porc+'" aria-valuemin="0" aria-valuemax="100"></div>'+
                  //         '</div>'+
                  //     '</div>'+
                  // '</div>'+
                  '<p class="mb-0 px-3 py-0" style="font-weight: 500;">Expira: <strong>' + minutos_e + '</strong></p>' + '</div>';
              }
              if (sms_t > 0) {
                porc = parseFloat(Math.round(minutos_r * 100) / sms_t).toFixed(0);
                if (porc > 100) {
                  porc = 100;
                }
                html += '<div class="col-12 col-md-4 pb-4 text-center">' + '<p class="mb-0 px-3 pb-1 pt-0 title-details">SMS</p>' + '<p class="mb-0 px-3 py-0" style="font-weight: 500;">SMS disponibles</p>' + '<p class="mb-0 px-3 py-0 title-details">' + sms_r + '</p>' +
                  // '<div class="col-10 col-sm-6 item-cont-details mt-auto mx-auto py-0">'+
                  //     '<div class="group-progress">'+
                  //         '<div class="progress mt-2 mb-3" style="height: 12px;border-radius: 30px;background: #DDD;">'+
                  //             '<div class="progress-bar progress-bar-success back-c-p" role="progressbar" style="border-radius:30px; width: '+porc+'%" aria-valuenow="'+porc+'" aria-valuemin="0" aria-valuemax="100"></div>'+
                  //         '</div>'+
                  //     '</div>'+
                  // '</div>'+
                  '<p class="mb-0 px-3 py-0" style="font-weight: 500;">Expira: <strong>' + sms_e + '</strong></p>' + '</div>';
              }
              // }
              $('#services-list').html(html);
            } else {
              $('#services-list').html('<div class="col-md-12">' + '<label class="control-label"> Sin servicios activos</label>' + '</div>');
            }
            if (res.msisdn.status == 'barring') {
              btnSusPar.hide();
              btnReactPar.show();
            } else if (res.msisdn.status == 'active' || res.msisdn.status == 'expired') {
              btnSusPar.show();
              btnReactPar.hide();
            }
            if ($('#modal_imei').text() != 'S/I' && res.msisdn.IMEI && $('#modal_imei').text() != res.msisdn.IMEI) {
              $('#modal_imei_new').show();
              $('#modal_imei_new span').text(res.msisdn.IMEI);
              $('#imei_form').val(res.msisdn.IMEI);
            } else {
              $('#modal_imei_new').hide();
            }
            //res.msisdn.is_band28 = 'N'; //Quitar luego de mostrar desarrollo
            $('#modal_b28 span').text(res.msisdn.is_band28 == 'Y' ? 'Si' : 'No');
            $('#modal_b28 a').hide();
            if (res.msisdn.is_band28 == 'N') {
              $('#modal_b28 a').show();
            }
            $('#modal_b28').show();
          }
          //Mostrando imei desde el profile
          if (res.msisdn.IMEI) {
            $('#modal_imei').html(res.msisdn.IMEI);
          }
          $('#modal_status').html(res.msisdn.status_es_concat);
          // if(res.msisdn.status == 'expired'){
          //     $("#retservice > option[data-days!='7'][value!='']").addClass('d-none')
          // }
          // else{
          //     $("#retservice > option[data-days!='7'][value!='']").removeClass('d-none')
          // }
          if (res.msisdn.status == 'active' || res.msisdn.status == 'expired') {
            btnSuspend.show();
          } else {
            if (res.msisdn.status == 'suspend' || res.msisdn.status == 'suspend_mobility' || res.msisdn.status == 'suspend_inactivity' || res.msisdn.status == 'suspend_mobility_nr' || res.msisdn.status == 'suspend_theftOrLoss') {
              btnActivate.show();
            }
          }
          //Mostrando opción para suspender por robo o extravío
          if (res.msisdn.status != 'suspend_theftOrLoss') {
            btnSuspTheft.show();
          }
          if (res.msisdn.status == 'suspend_theftOrLoss') {
            btnActCoo.hide();
          }
          if (res.msisdn.status == 'preactive') {
            btnActCoo.hide();
            btnSuspTheft.hide();
            btnCoo.hide();
          }
          /*if(res.msisdn.status == 'suspend_mobility'){
              $('#tabsuspension').removeClass('d-none');
          }
          else{
              $('#tabsuspension').addClass('d-none');
          }*/
          if (data !== null) {
            if (data.dn_type == 'H') {
              /*if(res.msisdn.status_es == 'Suspendido por movilidad'){
                  myMap(data.lat,data.lng,data.detc_lat,data.detc_lon);
              }
              else{*/
              myMap(data.lat, data.lng);
              //}
            }
          }
          $('#is-reduce').html(res.msisdn.is_reduced ? 'Activa' : 'Inactiva');
          if (res.msisdn.is_reduced) {
            $('#btn-reduce-content').show();
          } else {
            $('#btn-reduce-content').hide();
          }
          $('#modal_t_gbret').html(res.msisdn.total_rt_gb.toFixed(2) + " GB");
          $('#modal_r_gbret').html(res.msisdn.remaining_rt_gb.toFixed(2) + " GB");
          getSuspensionDetails('52' + $('#modal_msisdn').text());
          if (opendModal) {
            //$('#open_detail_btn').click();
            $('#detail').modal({
              keyboard: false,
              backdrop: 'static'
            });
          }
          if (data.status == 'Dado de baja') {
            $('#btn-refresh').hide();
            $('#service-t-cont').hide();
            $('#modal_suspend_btn').hide();
            $('#modal_activate_btn').hide();
            $('#modal_suspend_theft_btn').hide();
            $('#modal_suspend_par_btn').hide();
            $('#modal_activate_par_btn').hide();
            $('#btn-chcoor-modal').hide();
            $('#btn-active-chcoor').hide();
            $('#btn-reduce-content').hide();
          } else {
            getCompensationsEstatus($('#modal_msisdn').text());
          }
        } else if (res.status == 'error') {
          alert(res.message);
          $(".preloader").fadeOut();
        }
      }
    }, function(res) {
      $(".preloader").fadeOut();
      alert('Hubo un error tratando de consultar');
    });
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function reduceDeactivate() {
  try {
    if (confirm('¿Seguro que desea desactivar el servicio de reducción de velocidad?')) {
      doAction('reduce-deactivate', function(res) {
        if (res.status == 'success') {
          $('#is-reduce').html('Inactiva');
          $('#btn-reduce-content').hide();
        }
        alert(res.message);
      }, function(res) {
        alert('Hubo un error tratando de desactivar el servicio de reducción');
      });
    }
  } catch (e) {
    $(".preloader").fadeOut();
    console.log('Error catch ', e);
  }
}

function detail(client) {
  client = client.replace(/\\\'/g, '"').replace(/\'/g, '"');
  var object = JSON.parse(client);
  setModal('detail', object);
}

function editClient(client) {
  client = client.replace(/\\\'/g, '"').replace(/\'/g, '"');
  var object = JSON.parse(client);
  setModal('edit', object);
}

function clearInfo() {
  $('#origin_name').val('');
  $('#origin_name_last').val('');
  $('#origin_phone').val('');
  $('#origin_phone2').val('');
  $('#origin_email').val('');
  $('#temp_mailvalid').val('true');
  $('#origin_address').val('');
  $('#origin_users').val('');
  $('#cp_phone').val('');
  $('#cp_phone2').val('');
  // console.log('clear ok');
}
function clearView(){

  $('#modal_name').html('');
  $('#modal_phone').html('');
  $('#modal_phone_aux').html('');
  $('#modal_email').html('');
  $('#modal_address').html('');

  //Finciamiento
  $('#modal_financing').html('');
  $('#modal_financing_amount').html('');
  $('#modal_week_quote').html('');
  $('#modal_month_quote').html('');
  $('#modal_quin_quote').html('');
  $('#modal_quote_pay').html('');
  $('#modal_total_payment').html('');

  //Datos del vendedor
  $('#modal_name_seller').html('');
  $('#modal_org_seller').html('');
  $('#modal_date_up').html('');

  //Estatus de la linea
  $('#modal_msisdn').html(''); 
  $('#modal_time_recharge').html('');   


  $('#modal_equipo').html('');
  $('#modal_status').html('');
  $('#modal_imei').html('');
  $('#modal_service').html('');
  $('#modal_n_swap').html('');  
  $('#is-reduce').html(''); 

  $('#modal_gb').html('');
  $('#modal_date_expire').html('');

  $('#modal_broadband').html('');
  $('#modal_lat').html('');
  $('#modal_lng').html('');
  $('#modal_n_coord').html('');


  $('#modal_hbb_incidences').html('');
  $('#modal_hbb_date_incidences').html('');
  $('#modal_mifi').html('');


  $('#modal_marca').html('');
  $('#modal_modelo').html('');
  $('#label_imei_title').text('');
  $('#modal_service_sell').html('');

  //zona de Cobertura
  $('#modal_coverage_zone').html('');
  $('#modal_coverage_zone-content').hide();

  //Cantidad suspensiones Fibra
  $('#modal_total_suspends').html('');
  $('#modal_total_suspends-content').hide();

}
function setModal(modal, data) {
  if (data != undefined && data != null && data != '') {
    if (modal != undefined && modal != null && modal != '') {
      $('#editing_msisdn').val(data.msisdn);
      $('#modal_service_sell_container').hide();
      if (modal == 'edit') {
        $('#modal_edit_dn').html(data.msisdn);
        $('#modal_edit_name').val(data.client.name);
        $('#modal_edit_last_name').val(data.client.last_name);
        $('#modal_edit_phone').val(data.phone_home);
        $('#modal_edit_phone_2').val(data.client.phone);
        $('#modal_edit_email').val(data.email);
        if ($('#modal_edit_address')[0]) {
          new google.maps.places.Autocomplete($('#modal_edit_address')[0]);
        }
        $('#modal_edit_address').val(data.client.address);
        $('#origin_dni').val(data.client.dni);
        $('#origin_dn').val(data.msisdn);
        /*origin*/
        this.clearInfo();
        $('#origin_name').val(data.client.name);
        $('#origin_name_last').val(data.client.last_name);
        $('#origin_phone').val(data.phone_home);
        $('#origin_phone2').val(data.client.phone);
        $('#origin_email').val(data.email);
        $('#origin_address').val(data.client.address);
        $('#origin_users').val(data.seller.users_email);
        /*end origin*/
        $('#editModal').modal({
          keyboard: false,
          backdrop: 'static'
        });
      } else {
        $(".preloader").fadeIn();
        this.clearView();
        //Información del cliente
        this.clearView();
        $('#modal_name').html(data.name);
        $('#modal_phone').html(data.phone_home ? data.phone_home : 'S/I');
        $('#modal_phone_aux').html(data.client.phone ? data.client.phone : 'S/I');
        $('#modal_email').html(data.email ? data.email : 'S/I');
        $('#modal_address').html(data.client.address ? data.client.address : 'S/I');
        //Datos del financiamiento
        if (data.type_buy == 'CR') {
          $('#modal_financing').html(data.financing.name);
          $('#modal_financing_amount').html(data.financing.amount_financing);
          $('#modal_week_quote').html(data.financing.SEMANAL);
          $('#modal_month_quote').html(data.financing.MENSUAL);
          $('#modal_quin_quote').html(data.financing.QUINCENAL);
          $('#modal_quote_pay').html(data.num_dues);
          $('#modal_total_payment').html(data.price_remaining);
          $('#credit-content').show();
        } else {
          $('#credit-content').hide();
        }
        //Datos del vendedor
        if(data.seller != null){
          $('#modal_name_seller').html(data.seller.name + ' ' + data.seller.last_name);
          $('#modal_org_seller').html(data.seller.business_name ? data.seller.business_name : 'N/A');

        }else{
          $('#modal_name_seller').html('N/A');
          $('#modal_org_seller').html('N/A');

        }
        $('#modal_date_up').html(data.date_buy);
        if (data.typePayment) {
          $('#modal_bussines_financing').html(data.typePayment);
        } else {
          $('#modal_bussines_financing').html('N/A');
        }
        //Estatus de la linea
        $('#modal_msisdn').html(data.msisdn);
        $('#label_time_recharge_content').hide();
        if (data.timeRecharge > 60) {
          $('#label_time_recharge_content').show();
          $('#modal_time_recharge').html(data.timeRecharge ? data.timeRecharge + ' dias' : 'S/I');
        }
        //console.log('modelo: ', data.equipo.modelo);
        $('#modal_equipo').html(data.equipo ? data.equipo : 'S/I');
        $('#modal_status').html('');
        $('#modal_imei_new').hide();
        $('#modal_imei').html(data.imei ? data.imei : 'S/I');
        $('#modal_service').html(data.plan);
        $('#modal_n_swap').html(data.n_sim_swap ? data.n_sim_swap : 0);
        $('#is-reduce').html('NO');
        if (data.dn_type != 'F') {
          $('#salud-tab').data('lat', data.lat);
          $('#salud-tab').data('lng', data.lng);
        }
        $('#label_migrations_content').hide();
        $('#label_incidences_content').hide();
        $('#label_date_incidences_content').hide();
        $('#label_marca').show();
        $('#label_modelo').show();
        $('#label_imei_title').text('Imei: ');
        $('#modal_sim_swap').show();
        $('#modal_is_reduce').show();
        $('#modal_total_gb_ret').show();
        $('#modal_rest_gb_ret').show();
        if (data.dn_type != 'T') {
          $('#tabportabilidad').hide();
        } else {
          $('#tabportabilidad').show();
        }
        if (data.dn_type == 'H' || data.dn_type == 'M' || data.dn_type == 'MH') {
          $('.info-pho').hide();
          $('.modal_status').hide();
          $('#modal_gb-content').show();
          $('#modal_date_expire-content').show();
          $('#service-t-cont').hide();
          $('#modal_gb').html('');
          $('#modal_date_expire').html(data.date_expire);
          $('#salud-tab').show();
          $('#compensaciones-tab').show();
          $('#blim-tab').show();
          if (data.status == 'Activo') {
            $('#retention-tab').show();
          } else {
            $('#retention-tab').hide();
          }
          $('#consumos-tab').show();
          $('#buyback-tab').show();
          if (data.dn_type == 'H') {
            $('#modal_broadband-content').show();
            $('#modal_lat-content').show();
            $('#modal_lng-content').show();
            $('#modal_n_coord-content').show();
            $('#coord-content').show();
            $('#modal_broadband').html(data.serviceability ? data.serviceability : 'S/I');
            $('#modal_lat').html(data.lat);
            $('#modal_lng').html(data.lng);
            $('#modal_n_coord').html(data.n_update_coord ? data.n_update_coord : 0);
            $('#coordenadas-tab').show();
            $('#col-1').removeClass('col-md-12');
            $('#col-1').addClass('col-md-6');
            $('#col-2').show();
            $('#label_mifi').hide();
            $('#label_migrations_content').show();
            $('#modal_hbb_migrations').html(data.is_migrated);
            if (data.status_issue != 'OK') {
              $('#modal_hbb_incidences').html(data.status_issue);
              $('#label_incidences_content').show();
              $('#modal_hbb_date_incidences').html(data.date_issue);
              $('#label_date_incidences_content').show();
            }
          }
          if (data.dn_type == 'M' || data.dn_type == 'MH') {
            $('#modal_broadband-content').hide();
            $('#modal_lat-content').hide();
            $('#modal_lng-content').hide();
            $('#modal_n_coord-content').hide();
            $('#coord-content').hide();
            $('#col-1').removeClass('col-md-6');
            $('#col-1').addClass('col-md-12');
            $('#col-2').hide();
            if (data.dn_type == 'M') {
              $('#modal_mifi').html('Nacional');
            } else {
              $('#modal_mifi').html('Huella Altan');
            }
          }
          $('#btn-refresh').show();
        } else {
          if (data.dn_type == 'F') {
            $('#col-1').removeClass('col-md-12');
            $('#col-1').addClass('col-md-6');
            $('#col-2').show();
            $("#retention-tab").hide();
            $('#consumos-tab').hide();
            $('#salud-tab').hide();
            $('#compensaciones-tab').hide();
            $('#blim-tab').hide();
            $('#coordenadas-tab').hide();
            $('#suspension-tab').hide();
            $('#promocion-tab').hide();
            $('#btn-refresh').hide();
            $('#label_mifi').hide();
            $('#modal_broadband-content').hide();
            $('#label_marca').show();
            $('#modal_marca').html(data.brand ? data.brand : "S/I");
            $('#label_modelo').show();
            $('#modal_modelo').html(data.model ? data.model : "S/I");
            $('#label_imei_title').text('MAC Address: ');
            $('#modal_imei_new').hide();
            $('#modal_b28').hide();

            $('#modal_service_sell_container').show();
            $('#modal_service_sell').html(data.service_sell_description + ' - ' + data.service_sell_speed);
            $('#modal_service').html(data.plan + ' - ' + data.speed);

            $('#modal_lat-content').show();
            $('#modal_lat').html(data.lat ? data.lat : "S/I");
            $('#modal_gb-content').hide();
            $('#modal_lng-content').show();
            $('#modal_lng').html(data.lng ? data.lng : "S/I");
            $('#modal_date_expire-content').show();
            $('#modal_date_expire').html(data.date_expire ? data.date_expire : "S/I");
            $('#modal_n_coord-content').hide();
            $('#modal_sim_swap').hide();
            $('#modal_is_reduce').hide();
            $('#modal_total_gb_ret').hide();
            $('#modal_rest_gb_ret').hide();
            $('#compensation-cont').hide();
            $('#service-t-cont').hide();

            $('#modal_coverage_zone').html(data.zone_name ? data.zone_name : "S/I");
            $('#modal_coverage_zone-content').show();

            $('#modal_total_suspends').html(data.total_suspend ? data.total_suspend : "S/I");
            $('#modal_total_suspends-content').show();

          } else {
            $('#btn-refresh').show();
            $('#modal_broadband-content').hide();
            $('#modal_lat-content').hide();
            $('#modal_lng-content').hide();
            $('#modal_gb-content').hide();
            $('#modal_date_expire-content').hide();
            $('#modal_n_coord-content').hide();
            $('#service-t-cont').show();
            if ($('#coord-content').length) {
              $('#coord-content').hide();
            }
            $('#col-1').removeClass('col-md-6');
            $('#col-1').addClass('col-md-12');
            $('#col-2').hide();
            $('#modal-tables-comp').hide();
            $('#modal_marca').html(data.brand);
            $('#modal_modelo').html(data.model);
            $('.info-pho').show();
            $('.modal_status').show();
            $('#salud-tab').hide();
            $('#compensaciones-tab').hide();
            $('#blim-tab').hide();
            $('#retention-tab').hide();
            $('#coordenadas-tab').hide();
            $('#consumos-tab').hide();
            $('#buyback-tab').hide();
            $('#label_mifi').hide();
            $('#modal_coverage_zone-content').hide();
            $('#modal_total_suspends-content').hide();
          }
        }
        //Dn para cambio de coordenadas
        $('#dn_netwey').val(data.msisdn);
        refreshData(true, data);
      }
    }
  }
}

function isValidateForm() {
  $('#error_msisdn_file').html('');
  $('#error_msisdn_select').html('');
  $('#error_name_select').html('');
  if ($('#client_manual_check').is(':checked')) {
    b = validateFieldsSelect('msisdn_select', 'error_msisdn_select', 'Debe seleccionar los msisdn a a buscar');
  } else if ($('#client_file_check').is(':checked')) {
    b = validatFiles('msisdn_file', 'error_msisdn_file', 'Debe seleccionar el archivo CSV con la información de los msisdn', 'El archivo debe ser de extensión CSV');
  } else if ($('#client_name_manual_check').is(':checked')) {
    b = validateFieldsSelect('name_select', 'error_name_select', 'Debe seleccionar uno o mas nombres del listado');
  }
  return b;
}

function validateFieldsSelect(id, errorId, error) {
  $('#'.concat(errorId)).html('');
  if ((getSelectObject(id).getValue() == null) || (getSelectObject(id).getValue() == undefined) || (getSelectObject(id).getValue() == '')) {
    $('#'.concat(errorId)).html(error);
    return false;
  }
  return true;
}

function validateFields(id, errorId, error) {
  $('#'.concat(errorId)).html('');
  if (($('#'.concat(id)).val() == null) || ($('#'.concat(id)).val() == undefined) || ($('#'.concat(id)).val() == '')) {
    $('#'.concat(errorId)).html(error);
    return false;
  }
  return true;
}

function getBuyBack(dn) {
  $(".preloader").fadeIn();
  if (dn) {
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      async: true,
      url: 'api/client/buy-back',
      method: "POST",
      data: {
        'msisdn': dn
      },
      dataType: "json",
      success: function(res) {
        $('#last-call-content').attr('hidden', true);
        if (res.status == 'success') {
          if (res.data.showLastCall) {
            $('#last-status-buyback').html(res.data.acept == 'N' ? 'No' : 'Si');
            $('#last-comment-buyback').html(res.data.comment);
            $('#last-call-buyback').html(res.data.date);
            $('#last-call-content').attr('hidden', null);
          }
        } else {
          console.log('No se pudo consultar datos de la última llamada');
        }
        $(".preloader").fadeOut();
      },
      error: function(res) {
        $('#last-call-content').attr('hidden', true);
        alert('No se pudo consultar datos de la última llamada');
        $(".preloader").fadeOut();
      }
    });
  } else {
    alert('No se pudo consultar datos de la última llamada');
  }
}

function saveCallBuyBack() {
  $(".preloader").fadeIn();
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async: true,
    url: 'api/client/save-call-buy-back',
    method: "POST",
    data: $('#form-buyback').serialize() + '&msisdn=' + $('#dn_netwey').val(),
    dataType: "json",
    success: function(res) {
      if (res.status == 'success') {
        alert('Datos guardados exitosamente.');
        $('#answer-buyback').attr("checked", null);
        $('#acept-buyback').attr("checked", null);
        $('#comment-buyback').val('');
        getBuyBack($('#dn_netwey').val());
      } else {
        alert('No se pudieron guardar los datos de la llamada.');
        $(".preloader").fadeOut();
      }
    },
    error: function(res) {
      alert('No se pudieron guardar los datos de la llamada.');
      $(".preloader").fadeOut();
    }
  });
}

function getHealthNetwork(msisdn) {
  $(".preloader").fadeIn();
  var URL = 'api/client/altan/health-network';
  var params = new FormData();
  //params.append('token', $('meta[name="csrf-token"]').attr('content'));
  params.append('dn', msisdn);
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      $('#modal_health_service').removeClass('text-success');
      $('#modal_health_service').removeClass('text-mywarning');
      $('#modal_health_service').removeClass('text-mydanger');
      if (res.status) {
        if (res.status == 'success') {
          $('#modal_health_data').removeClass('d-none');
          /*addr = res.address.split(',');
          $('#modal_health_lat').html(addr[0]);
          $('#modal_health_lng').html(addr[1]);*/
          $('#modal_coord_activate').html(res.hm_lat_lon);
          $('#modal_coord_traffic').html(res.tf_lat_lon);
          $('#modal_health_date').html(res.coord_timestamp);
          $('#modal_health_nodo').html(res.dist_hm_bs);
          $('#modal_healt_titleppal').html('Estado de la red de servicios');
          $('#modal_health_service_title').html(res.status_net);
          if (res.node.saturation != null && res.node.saturation != '') {
            switch (res.node.saturation.toLowerCase()) {
              case ('normal'):
                $('#modal_health_service').addClass('text-success');
                $('#modal_health_service_description').html('El nodo se encuentra funcionando de manera normal');
                break;
              case ('restricted'):
                $('#modal_health_service').addClass('text-mywarning');
                $('#modal_health_service_description').html('Se estan presentando restricciones');
                break;
              case ('blocked'):
                $('#modal_health_service').addClass('text-mydanger');
                $('#modal_health_service_description').html('El nodo esta presentando obstrucciones');
                break;
              case ('high_occupancy'):
                $('#modal_health_service').addClass('text-mydanger');
                $('#modal_health_service_description').html('El nodo esta presentando alta ocupacion');
                break;
              default:
                $('#modal_health_data').addClass('d-none');
                $('#modal_health_service').addClass('text-mydanger');
                $('#modal_healt_titleppal').html('');
                $('#modal_health_service_title').html('ERROR');
                $('#modal_health_service_description').html('Ocurrio un error enviando los datos');
                break;
            }
          }
        } else {
          $('#modal_health_data').addClass('d-none');
          $('#modal_health_service').addClass('text-mydanger');
          $('#modal_healt_titleppal').html('');
          $('#modal_health_service_title').html('ERROR');
          // $('#modal_health_service_description').html(res.message);
          $('#modal_health_service_description').html('Por los momentos el DN no posee informacion, intente mas tarde');
        }
      } else {
        $('#modal_health_data').addClass('d-none');
        $('#modal_health_service').addClass('text-mydanger');
        $('#modal_healt_titleppal').html('');
        $('#modal_health_service_title').html('ERROR');
        $('#modal_health_service_description').html('Ocurrio un error enviando los datos');
      }
      $(".preloader").fadeOut();
      //console.log(res.status);
      //console.log(res.data);
    },
    error: function(res) {
      $(".preloader").fadeOut();
      //console.log(res.status);
      //console.log(res.data);
    }
  });
}

function getCoordinatesChanges(dn) {
  $(".preloader").fadeIn();
  var URL = 'api/client/altan/coordinates-changes';
  var params = new FormData();
  //params.append('token', $('meta[name="csrf-token"]').attr('content'));
  params.append('dn', dn);
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      //console.log(res);
      $('#modal-coordinates-container').html(res.html);
      $(".preloader").fadeOut();
    },
    error: function(res) {
      console.log('Error: ', res);
      $(".preloader").fadeOut();
      //console.log(res.status);
      //console.log(res.data);
    }
  });
}

function getCompensationsEstatus(dn) {
  /*$(".preloader").fadeIn();*/
  var URL = 'api/client/altan/compensation-bonus';
  var params = new FormData();
  //params.append('token', $('meta[name="csrf-token"]').attr('content'));
  params.append('dn', dn);
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      //console.log(res);
      if (res['status'] == 'success') {
        var data = JSON.parse(JSON.stringify(res['msisdn']));
        //console.log(data['compensation_bonus']);
        if (data['compensation_bonus']) {
          $('#compensation-cont').show();
          $('#modal_compensation_description').text(data['compensation_bonus']['description']);
          $('#modal_compensation_totalmb').text(data['compensation_bonus']['total-mb']);
          $('#modal_compensation_remainingmb').text(data['compensation_bonus']['remaining-mb']);
          expiredate = moment("1900-01-01 00:00:00", "YYYY-MM-DD HH:mm:ss");
          data['compensation_bonus']['supplementaryOffers'].forEach((item, index) => {
            itemexp = moment(item['expireDate'], "YYYY-MM-DD HH:mm:ss")
            if (itemexp.diff(expiredate) > 0) expiredate = itemexp;
          });
          //console.log(expiredate.format('YYYY-MM-DD HH:mm:ss'));
          $('#modal_compensation_expiredate').text(expiredate.format('YYYY-MM-DD HH:mm:ss'));
        } else {
          $('#compensation-cont').hide();
        }
      } else {
        $('#compensation-cont').hide();
      }
      $(".preloader").fadeOut();
    },
    error: function(res) {
      console.log('Error ', res);
      $(".preloader").fadeOut();
      //console.log(res.status);
      //console.log(res.data);
    }
  });
}

function getSuspensionDetails(dn) {
  $(".preloader").fadeIn();
  var URL = 'api/client/suspension-details';
  var params = new FormData();
  //params.append('token', $('meta[name="csrf-token"]').attr('content'));
  params.append('dn', dn);
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    contentType: false,
    processData: false,
    async: true,
    url: URL,
    method: "POST",
    data: params,
    dataType: "json",
    success: function(res) {
      //console.log(res);
      //$('#modal-coordinates-container').html(res.html);
      if (res.error == false) {
        $('#modal_suspension_status').html('Suspendido por Movilidad');
        $('#modal_suspension_date').html(res.date);
        $('#modal_suspension_distance').html(res.distance + ' Km');
        $('#modal_suspension_consumption').html(res.consuption + ' Mb');
        if ($('#modal_status').text() == 'Suspendido por movilidad') {
          $('#tabsuspension').removeClass('d-none');
        } else {
          $('#tabsuspension').addClass('d-none');
        }
      } else {
        $('#modal_suspension_status').html('');
        $('#modal_suspension_date').html('');
        $('#modal_suspension_distance').html('');
        $('#modal_suspension_consumption').html('');
        $('#tabsuspension').addClass('d-none');
        //alert('Ocurrió un error consultando el detalle de la suspension');
      }
      $(".preloader").fadeOut();
    },
    error: function(res) {
      console.log('Error ', res);
      $('#modal_suspension_status').html('');
      $('#modal_suspension_date').html('');
      $('#modal_suspension_distance').html('');
      $('#modal_suspension_consumption').html('');
      alert('Ocurrió un error consultando el detalle de la suspension');
      $(".preloader").fadeOut();
      //console.log(res.status);
      //console.log(res.data);
    }
  });
}

function authorizeKeySwal(nameprnt = null) {
  if (nameprnt) {
    namecoord = "(" + nameprnt + ")";
  }
  swal({
    title: "Requiere Autorización",
    text: "Haz superado el limite de activaciones permitidas, para continuar es necesario que ingreses la clave de autorización de tu supervisor " + namecoord,
    content: {
      element: "input",
      required: "required",
      attributes: {
        placeholder: "clave de autorización",
        type: "password",
      },
    },
    buttons: true,
    closeOnEsc: false,
    closeOnClickOutside: false,
  }).then((password) => {
    if (password.trim() == "") {
      swal("Debe ingresar una clave de autorización", {
        className: "text-success",
      }).then(() => {
        authorizeKeySwal(nameprnt);
      });
    } else {
      actRetentionService(password);
    }
  });
}

function actRetentionService(pass = "") {
  //ser=$('#form-actRetentionService').serialize()
  //console.log(ser);
  if ($('#retservice').val() == "" || $('#retreason').val() == "" || $('#retsubreason').val() == "") {
    alert('Debes seleccionar el servicio y el motivo que quieres activar');
    return;
  }
  $(".preloader").fadeIn();
  if (pass != "") {
    data = $('#form-actRetentionService').serialize() + '&msisdn=' + $('#dn_netwey').val() + '&pass=' + pass;
  } else {
    data = $('#form-actRetentionService').serialize() + '&msisdn=' + $('#dn_netwey').val();
  }
  $.ajax({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    },
    async: true,
    url: 'api/client/activate-retention-service',
    method: "POST",
    data: data,
    dataType: "json",
    success: function(res) {
      //console.log(res);
      if (res.status == 'success') {
        $(".preloader").fadeOut();
        if (res.action == 'authorize') {
          authorizeKeySwal(res.name_parent);
        } else {
          alert(res.msg);
          $('#retservice').val('');
          $('#retreason').val('');
          $('option.subreason[data-reason!="' + reason + '"]').addClass('d-none');
          $('option.subreason[data-reason="' + reason + '"]').removeClass('d-none');
          $('#retsubreason').val("");
          refreshData(false);
          refreshRetentions();
        }
        $(".preloader").fadeOut();
      } else {
        alert('No se pudo realizar la activación: ' + res.msg);
        $(".preloader").fadeOut();
      }
    },
    error: function(res) {
      //console.log(res);
      alert('Ocurrió un error, no se pudo realizar la activación.');
      $(".preloader").fadeOut();
    }
  });
}
/*function getCompensationsHistory(msisdn){
    getview('client/datatable-compensations/'+msisdn,'modal-tables', false);
}*/
function getTabPortability() {
  var dn = $('#dn_netwey').val();
  if (dn && dn != '') {
    $('.preloader').show();
    $.ajax({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      },
      async: true,
      url: "view/port/portFromNew",
      method: 'POST',
      data: {
        msisdn: dn
      },
      dataType: 'json',
      success: function(res) {
        $(".preloader").fadeOut();
        if (res.success) {
          $('#blockPortability').html(res.htmlCode);
        } else {
          alert(res.msg);
          setTimeout(function() {
            $('#detalles-tab').trigger('click');
          }, 100);
        }
      },
      error: function(res) {
        alert('No se pudo mostrar el formulario de portabilidad.');
        setTimeout(function() {
          $(".preloader").fadeOut();
          $('#detalles-tab').trigger('click');
        }, 100);
      }
    });
  }
}
$(document).ready(function() {
  var configSelect = {
    valueField: 'msisdn',
    labelField: 'msisdn',
    searchField: 'msisdn',
    options: [],
    create: false,
    persist: false,
    render: {
      option: function(item, escape) {
        return '<p>' + item.msisdn + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/client/get-clients-input',
        type: 'POST',
        dataType: 'json',
        data: {
          q: query
        },
        error: function() {
          callback();
        },
        success: function(res) {
          if (res.success) callback(res.clients);
          else callback();
        }
      });
    }
  };
  var configSelect2 = {
    valueField: 'dni',
    labelField: 'full_name',
    searchField: 'full_name',
    options: [],
    create: false,
    render: {
      option: function(item, escape) {
        return '<p>' + item.full_name + '</p>';
      }
    },
    load: function(query, callback) {
      if (!query.length) return callback();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        url: 'api/client/get-clients-by-name',
        type: 'POST',
        dataType: 'json',
        data: {
          q: query
        },
        error: function() {
          callback();
        },
        success: function(res) {
          if (res.success) callback(res.clients);
          else callback();
        }
      });
    }
  };
  $('#msisdn_select').selectize(configSelect);
  $('#name_select').selectize(configSelect2);
  $('#error_msisdn_file').html('');
  $('#error_msisdn_select').html('');
  $('#error_name_select').html('');
  $('#msisdn_select_container').hide();
  $('#name_select_container').hide();
  $('#msisdn_file_container').hide();
  $('#client_manual_check').click(function() {
    $('#error_msisdn_file').html('');
    $('#error_msisdn_select').html('');
    $('#error_name_select').html('');
    if ($('#client_manual_check').is(':checked')) {
      validateFieldsSelect('msisdn_select', 'error_msisdn_select', 'Debe seleccionar los msisdn a buscar');
      $('#msisdn_file_container').hide();
      $('#client_file_check').prop('checked', false);
      $('#name_select_container').hide();
      $('#client_name_manual_check').prop('checked', false);
      $('#msisdn_select_container').show();
    } else {
      $('#msisdn_file_container').hide();
      $('#msisdn_select_container').hide();
      $('#name_select_container').hide();
    }
  });
  $('#client_file_check').click(function() {
    $('#error_msisdn_file').html('');
    $('#error_msisdn_select').html('');
    $('#error_name_select').html('');
    if ($('#client_file_check').is(':checked')) {
      validatFiles('msisdn_file', 'error_msisdn_file', 'Debe seleccionar el archivo CSV con la información de los msisdn', 'El archivo debe ser de extensión CSV');
      $('#msisdn_file_container').show();
      $('#msisdn_select_container').hide();
      $('#client_manual_check').prop('checked', false);
      $('#name_select_container').hide();
      $('#client_name_manual_check').prop('checked', false);
    } else {
      $('#msisdn_file_container').hide();
      $('#msisdn_select_container').hide();
      $('#name_select_container').hide();
    }
  });
  $('#client_name_manual_check').click(function() {
    $('#error_msisdn_file').html('');
    $('#error_msisdn_select').html('');
    $('#error_name_select').html('');
    if ($('#client_name_manual_check').is(':checked')) {
      validateFieldsSelect('name_select', 'error_name_select', 'Debe seleccionar los nombres a buscar');
      $('#msisdn_file_container').hide();
      $('#client_file_check').prop('checked', false);
      $('#msisdn_select_container').hide();
      $('#client_manual_check').prop('checked', false);
      $('#name_select_container').show();
    } else {
      $('#msisdn_file_container').hide();
      $('#msisdn_select_container').hide();
      $('#name_select_container').hide();
    }
  });
  $('#change_coor_form').validate({
    rules: {
      lat: {
        required: true,
        number: true
      },
      lng: {
        required: true,
        number: true
      }
    },
    messages: {
      lat: {
        required: "Por favor ingrese una latitud",
        number: "Por favor ingrese una latitud valida"
      },
      lng: {
        required: "Por favor ingrese una longitud",
        number: "Por favor ingrese una longitud valida"
      }
    }
  });
  $('#btn-active-chcoor').on('click', function(e) {
    var dn = $('#editing_msisdn').val();
    if (dn && dn != '') {
      $('.preloader').show();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        url: "api/client/activate-change-coord",
        method: 'POST',
        data: {
          msisdn: dn
        },
        dataType: 'json',
        success: function(res) {
          $(".preloader").fadeOut();
          if (!res.success) {
            alert('No se pudo habilitar el Servicio.');
          } else {
            alert('Servicio habilitado exitosamente.');
            refreshData(false);
          }
        },
        error: function(res) {
          alert('No se pudo habilitar el Servicio.');
        }
      });
    }
  });
  $('#modal_changecoor').on('show.bs.modal', function(e) {
    var dn = $('#modal_changecoor #dn_netwey').val();
    if (dn && dn != '') {
      $('.preloader').show();
      $.ajax({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        url: "api/client/canUpdatelatlng",
        method: 'POST',
        data: {
          msisdn: dn
        },
        dataType: 'json',
        success: function(res) {
          $(".preloader").fadeOut();
          if (!res.success) {
            alert(res.msg);
            setTimeout(function() {
              $('#modal_changecoor .close').trigger('click');
            }, 100);
          }
        },
        error: function(res) {
          alert('No se pudo consultar pago por cambio de coordenadas.');
          setTimeout(function() {
            $(".preloader").fadeOut();
            $('#modal_changecoor .close').trigger('click');
          }, 100);
        }
      });
    }
  });
  $('[href=#portability]').on('shown.bs.tab', function(e) {
    getTabPortability();
  });
});