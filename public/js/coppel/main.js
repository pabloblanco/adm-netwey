var fv;

function setModal(object) {
    if (object != null) {
        $('#fail_id').val(object.id);
        $('#fail_dn').val(object.msisdn);
        $('#fail_client').val(object.client);
        $('#fail_seller').val(object.seller);
        $('#fail_article').val(object.article);
        $('#fail_pack').val(object.pack);
        $('#fail_service').val(object.service);
        $('#fail_date').val(object.date_register);

        $('#valid-btn').data('id',object.id);

    } else {
        $('#fail_id').val('');
        $('#fail_dn').val('');
        $('#fail_client').val('');
        $('#fail_seller').val('');
        $('#fail_article').val('');
        $('#fail_pack').val('');
        $('#fail_service').val('');
        $('#fail_date').val('');

        $('#valid-btn').data('id',null);

    }

    $('#new_msisdn').val('');
    $('#new_client').val('');
    $('#new_seller').val('');
    $('#new_article').val('');
    $('#new_pack').val('');
    $('#new_service').val('');
    $('#new_date').val('');
    $('#associate-btn').attr('disabled',true)
}

$('#myModal').on('hidden.bs.modal', function () {
    setModal(null);
});

function corregir (object) {
    obj = JSON.parse(object.replace(/\'/g, '"'));
    setModal(obj);
    $('#myModal').modal({backdrop: 'static', keyboard: false});
}

$(document).ready(function () {
    $('#valid-btn').data('id',null);

    drawTable = function(){
        if ($.fn.DataTable.isDataTable('#myTable')){
            $('#myTable').DataTable().destroy();
        }

        ordercol=0;
        columnss = [];

        if ( $('th#actionCol').length ) {
            ordercol=1;
            columnss = [
                {data: null, render: function(data,type,row,meta){
                    html = '';
                    // if (row.action){
                        jsoncad=JSON.stringify(row).replace(/"/g, '\\\'');
                        html = html + '<button type="button" data-id="'+row.id+'" class="btn btn-info btn-md edit-bc" onclick="corregir(\''+jsoncad+'\')">Corregir</button>';
                    // }
                    return html;
                }, searchable: false, orderable: false}
            ];
        }

        columnss.push({data: 'id'});
        columnss.push({data: 'msisdn'});
        columnss.push({data: 'client'});
        columnss.push({data: 'seller'});
        columnss.push({data: 'article'});
        columnss.push({data: 'pack'});
        columnss.push({data: 'service'});
        columnss.push({data: 'error'});
        columnss.push({data: 'date_register'});

        $('.preloader').show();

        $('#myTable').DataTable({
            searching: false,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'api/coppel/list-dt',
                data: function (d) {
                    d._token = $('meta[name="csrf-token"]').attr('content');
                },
                type: "POST"
            },
            initComplete: function(settings, json){
                // $('.delete-fi').bind('click', deleteFinan);
                // $('.edit-fi').bind('click', editFinan);;
                $(".preloader").fadeOut();
            },
            order: [[ ordercol, "desc" ]],
            deferRender: true,
            columns: columnss
        });
    }

    drawTable();

    $("#open_modal_btn").on('click',()=>{ $("#myModal").modal(); });

    $('#valid-btn').on('click',()=>{
        if($('#valid-btn').data('id') && $('#new_msisdn').val().length == 10){
            $.ajax({
                url: 'api/coppel/valid-sustitute',
                type: 'POST',
                data:{
                    id: $('#valid-btn').data('id'),
                    msisdn : $('#new_msisdn').val(),
                    _token : $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                success: function(result) {
                    if(result.success){
                        data=result.data;
                        $('#new_client').val(data.client);
                        $('#new_seller').val(data.seller);
                        $('#new_article').val(data.article);
                        $('#new_pack').val(data.pack);
                        $('#new_service').val(data.service);
                        $('#new_date').val(data.date_register);
                        $('#associate-btn').attr('disabled',false)
                    }
                    else{
                        swal({
                          title: result.msg,
                          text: 'por favor verifique e intente nuevamente',
                          icon: "error",
                        });
                    }
                },
                error: function(){
                    swal({
                          title:'Ocurrio un error al validar DN sustituto',
                          text: 'por favor intente nuevamente',
                          icon: "error",
                        });
                }
            });
        }
        else{
            swal({
              title:'DN Sustituto no es válido',
              text: 'por favor verifique e intente nuevamente',
              icon: "error",
            });
        }
    });

    $('#new_msisdn').on('input', () => {
        $('#new_client').val('');
        $('#new_seller').val('');
        $('#new_article').val('');
        $('#new_pack').val('');
        $('#new_service').val('');
        $('#new_date').val('');
        $('#associate-btn').attr('disabled',true)
    });

    $('#associate-btn').on('click', () => {
        $.ajax({
            url: 'api/coppel/associate-sustitute',
            type: 'POST',
            data:{
                id: $('#valid-btn').data('id'),
                msisdn : $('#new_msisdn').val(),
                _token : $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json',
            success: function(result) {
                if(result.success){
                   swal({
                      title: result.msg,
                      icon: "success",
                    }).then(() => {
                      $(".edit-bc[data-id="+$('#valid-btn').data('id')+"]").prop('disabled',true);
                      $('#myModal .close').trigger('click');
                    });
                }
                else{
                    swal({
                      title: result.msg,
                      text: 'por favor verifique e intente nuevamente',
                      icon: "error",
                    });
                }
            },
            error: function(){
                swal({
                  title:'Ocurrio un error realizando la asociación',
                  text: 'por favor intente nuevamente',
                  icon: "error",
                });
            }
        });
    });
});