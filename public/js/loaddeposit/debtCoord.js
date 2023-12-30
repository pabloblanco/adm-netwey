drawTable = (msg = '') => {
  if ($.fn.DataTable.isDataTable('#listuserDebt')){
      $('#listuserDebt').DataTable().destroy();
  }

  $('.preloader').show();

  $('#listuserDebt').DataTable({
      searching: true,
      processing: true,
      serverSide: true,
      paging: false,
      ajax: {
          url: "api/seller/get_coord_debt",
          data: function (d) {
              d._token = $('meta[name="csrf-token"]').attr('content');
          },
          type: "POST"
      },
      initComplete: function(settings, json){
          $('#concAll').prop('checked', null)
          $(".preloader").fadeOut();

          if(msg != '') alert(msg);
      },
      deferRender: true,
      order: [[3, 'desc'],[ 4, "desc" ]],
      columns: [
          {data: 'name'},
          {data: 'id_deposit'},
          {data: 'residue_amount'},
          {
              data: 'debt',
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.debt != '$0')
                      html = '<a href="#" data-email="'+row.email+'" data-type="last" onclick="detailModal(\''+row.email+'\',\'last\');">'+row.debt+'</a>';
                  else
                      html = row.debt

                  return html;
              },
              searchable: false,
              orderable: true
          },
          {data: 'days_old_deb',
              render: function(data,type,row,meta){
                  var html = "";
                  if(row.alert_days_deb){
                      html = "<p style='color:red; font-weight:700;' >"+ row.days_old_deb +"</p>";
                  }
                  else{
                      html = "<p>"+ row.days_old_deb +"</p>";
                  }

                  return html;
              },
              searchable: false,
              orderable: true
          },
          {
              data: null,
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.debt_today != '$0')
                      html = '<a href="#" data-email="'+row.email+'" data-type="today" onclick="detailModal(\''+row.email+'\',\'today\');">'+row.debt_today+'</a>';
                  else
                      html = row.debt_today

                  return html;
              },
              searchable: false,
              orderable: false
          },
          {
              data: null,
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.debt_sellers_old != '$0')
                      html = '<a href="#" data-email="'+row.email+'" data-type="last" onclick="detailModalSellers(\''+row.email+'\',\'last\');">'+row.debt_sellers_old+'</a>';
                  else
                      html = row.debt_sellers_old

                  return html;
              },
              searchable: false,
              orderable: false
          },
          {
              data: null,
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.debt_sellers_today != '$0')
                      html = '<a href="#" data-email="'+row.email+'" data-type="today" onclick="detailModalSellers(\''+row.email+'\',\'today\');">'+row.debt_sellers_today+'</a>';
                  else
                      html = row.debt_sellers_today

                  return html;
              },
              searchable: false,
              orderable: false
          },
          {
              data: null,
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.debtIns != '$0')
                      html = '<a href="#" data-email="'+row.email+'" onclick="detailModalInst(\''+row.email+'\')">'+row.debtIns+'</a>';
                  else
                      html = row.debtIns

                  return html;
              },
              searchable: false,
              orderable: false
          },
          {data: 'deposits', searchable: false},
          {
              data: null,
              render: function(data,type,row,meta){
                  var html = '';
                  if(row.last_conc != 'N/A')
                      html = '<a href="#" data-email="'+row.email+'" onclick="lastDepModal(\''+row.email+'\')">'+row.last_conc+'</a>';
                  else
                      html = row.last_conc

                  return html;
              },
              searchable: false,
              orderable: false
          }
      ]
  });
}

sales = (e) => {
  e.preventDefault();
  var sale = $(e.currentTarget).data('sale');

  if(sale && sale != ''){
      if(!$('.'+sale).is(':visible')){
          $('.'+sale).attr('hidden', null);
          $(e.currentTarget).text('Ocultar ventas');
      }
      else{
          $('.'+sale).attr('hidden', true);
          $(e.currentTarget).text('Ver ventas');
      }
  }
}

detailModal = (email,type) => {
  if(email && email != ''){
      $('.preloader').show();

      $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: "api/seller/detail_debt",
          method: 'POST',
          data: {email: email, type:type},
          dataType: 'json',
          success: function (res) {
              $(".preloader").fadeOut();

              if(res.success){
                  $('#detailModal .modal-body').html(res.html);
                  $('.seeSales').on('click', sales);
                  $('#detailModal').modal({backdrop: 'static', keyboard: false});
              }else{
                  alert(res.msg);
                  $(".preloader").fadeOut();
              }
          },
          error: function (res) {
              alert('No se pudo cargar el detalle de la deuda.');
              $(".preloader").fadeOut();
          }
      });
  }
}

detailModalSellers = (email,type) => {
  if(email && email != ''){
      $('.preloader').show();

      $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: 'api/seller/detail_debt_sellers',
          method: 'POST',
          data: {email: email, type:type},
          dataType: 'json',
          success: function (res) {
              $(".preloader").fadeOut();

              if(res.success){
                  $('#detailModalSellers .modal-body').html(res.html);
                  $('.seeSales').on('click', sales);
                  $('#detailModalSellers').modal({backdrop: 'static', keyboard: false});
              }else{
                  alert(res.msg);
                  $(".preloader").fadeOut();
              }
          },
          error: function (res) {
              alert('No se pudo cargar el detalle de la deuda.');
              $(".preloader").fadeOut();
          }
      });
  }
}

salesInst = (e) => {
      e.preventDefault();

      var sale = $(e.currentTarget).data('sale');

      if(sale && sale != ''){
          if(!$('.'+sale).is(':visible')){
              $('.'+sale).attr('hidden', null);
              $(e.currentTarget).text('Ocultar ventas');
          }
          else{
              $('.'+sale).attr('hidden', true);
              $(e.currentTarget).text('Ver ventas');
          }
      }
  }

detailModalInst = (email) => {
  if(email && email != ''){
      $('.preloader').show();

      $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: "api/seller/detail_debt_inst",
          method: 'POST',
          data: {email: email},
          dataType: 'json',
          success: function (res) {
              $(".preloader").fadeOut();

              if(res.success){
                  $('#detailModalInst .modal-body').html(res.html);
                  $('.seeSalesInst').on('click', salesInst);
                  $('#detailModalInst').modal({backdrop: 'static', keyboard: false});
              }else{
                  alert(res.msg);
                  $(".preloader").fadeOut();
              }
          },
          error: function (res) {
              alert('No se pudo cargar el detalle de la deuda.');
              $(".preloader").fadeOut();
          }
      });
  }
}

lastDepModal = (email) => {
  if(email && email != ''){
      $('.preloader').show();

      $.ajax({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          },
          async: true,
          url: "api/seller/get_lasts_deposits",
          method: 'POST',
          data: {email: email},
          dataType: 'json',
          success: function (res) {
              $(".preloader").fadeOut();

              if(res.success){
                  $('#lastDepModal .modal-body').html(res.html);
                  $('#lastDepModal').modal({backdrop: 'static', keyboard: false});
              }else{
                  alert(res.msg);
                  $(".preloader").fadeOut();
              }
          },
          error: function (res) {
              alert('No se pudo cargar los últimas depósitos del usuario.');
              $(".preloader").fadeOut();
          }
      });
  }
}

/*opeAndFormat = function(val1, val2, ope){
  let total = 0;

  const formatAmount = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  });

  val1 = val1.toString();
  val2 = val2.toString();

  val1 = val1.replaceAll('$','');
  val1 = val1.replaceAll(' ','');

  val2 = val2.replaceAll('$','');
  val2 = val2.replaceAll(' ','');

  val1 = parseFloat(val1);
  val2 = parseFloat(val2);

  if(ope == '-'){
      total = val1 - val2;
  }

  if(ope == '+'){
      total = val1 + val2;
  }
  
  return formatAmount.format(total);
}*/

$(document).ready( () => {
  $('#detailModal .close').on('click', function (event){
      $('#detailModal .modal-body').html('');
  });

  //-----------------------------------------------------------------------//
  $('#detailModalSellers .close').on('click', function (event){
      $('#detailModalSellers .modal-body').html('');
  });

  //-----------------------------------------------------------------------//
  $('#detailModalInst .close').on('click', function (event){
      $('#detailModalInst .modal-body').html('');
  });

  //-----------------------------------------------------------------------//
  $('#lastDepModal .close').on('click', function (event){
      $('#lastDepModal .modal-body').html('');
  });

  drawTable();
});