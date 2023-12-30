drawTable = (filter,msg = '') => {
    status = userStatus();

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
            url: "api/seller/get_user_debt",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.filter = filter;
                d.status = status;
            },
            type: "POST"
        },
        initComplete: function(settings, json){
            $('#concAll').prop('checked', null)
            $(".preloader").fadeOut();

            if(msg != '') alert(msg);
        },
        deferRender: true,
        order: [[4, 'desc'],[ 5, "desc" ]],
        columns: [
            {
                data: null,
                render: function(data,type,row,meta){
                    var html = '<button type="button" class="btn btn-info btn-md" data-cod="'+row.cod+'" data-name="'+row.name+'" style="width: 115px;" onclick="manualLoad(\''+row.cod+'\',\''+row.name+'\')">Insertar</button>';
                    html += '<button type="button" class="btn btn-danger btn-md" data-email="'+row.email+'" style="width: 115px;" onclick="deleteDepModal(\''+row.email+'\')">Limpiar</button>';
                    
                    if(row.status != 'D'){
                        html += '<button type="button" class="btn btn-warning btn-md lock" style="width: 115px;" onclick="lockedUser(\''+row.email+'\')" '+(row.is_locked != 'N' ? 'hidden' : '')+'>Bloquear</button>';
                        html += '<button type="button" class="btn btn-success btn-md unlock" style="width: 115px;" onclick="unLockedUser(\''+row.email+'\')" '+(row.is_locked != 'Y' ? 'hidden' : '')+'>Desbloquear</button>';
                    }
                    
                    return html;
                },
                searchable: false,
                orderable: false
            },
            {data: 'name'},
            {data: 'email',visible:false},
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
                    //console.log(row);
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
            },
            {
                data: null,
                render: function(data, type, row, meta){
                    var html = '<input type="checkbox" name="conciliate" value="'+row.email+'">';
                    return html;
                },
                searchable: false,
                orderable: false
            }
        ]
    });
}

lockedUser = (email) => {
    let target = this.event.target;
    $('.preloader').show();

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        url: "api/seller/locked_user",
        method: 'POST',
        data: {email: email},
        dataType: 'json',
        success: function (res) {
            if(res.success){
                $(target).attr('hidden', true);
                $(target).siblings('.unlock').attr('hidden', null);

                alert('Usuario bloqueado exitosamente.');
                
                $(".preloader").fadeOut();
            }else{
                $(".preloader").fadeOut();
                alert(res.msg);
            }
        },
        error: function (res) {
            alert('No se pudo bloquear el usuario.');
            $(".preloader").fadeOut();
        }
    });
}

unLockedUser = (email) => {
    let target = this.event.target;
    $('.preloader').show();

    $.ajax({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        async: true,
        url: "api/seller/un_locked_user",
        method: 'POST',
        data: {email: email},
        dataType: 'json',
        success: function (res) {
            if(res.success){
                $(target).attr('hidden', true);
                $(target).siblings('.lock').attr('hidden', null);

                alert('Usuario desbloqueado exitosamente.');

                $(".preloader").fadeOut();
            }else{
                $(".preloader").fadeOut();
                alert(res.msg);
            }
        },
        error: function (res) {
            alert('No se pudo desbloquear el usuario.');
            $(".preloader").fadeOut();
        }
    });
}

manualLoad = (cod,name) => {
    if(cod && cod != ''){
        $('#cod').val(cod);
        $('#userDep').text(name);
        $('#manualLoad').modal({backdrop: 'static', keyboard: false});
    }
}

deleteDep = (e) => {
    var email = $(e.currentTarget).data('email');
    if(email && email != ''){
        $('.preloader').show();

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: true,
            url: "api/seller/delete_last_deposit",
            method: 'POST',
            data: {email: email},
            dataType: 'json',
            success: function (res) {
                if(res.success){
                    $('#deleteDepModal .close').trigger('click');
                    alert('Depósito eliminado exitosamente.');

                    let table = $('#listuserDebt').DataTable();
                    let index = table.row('#'+res.code).index();
                    let cell = table.cell({row:index, column:9});
                    let data = cell.data();

                    data.deposits = opeAndFormat(res.amount, data.deposits, '-');
                    cell.data(data).draw();
                    //drawTable($('#org').val());
                }else{
                    $(".preloader").fadeOut();
                    alert(res.msg);
                }
            },
            error: function (res) {
                alert('No se pudo eliminar el depósito.');
                $(".preloader").fadeOut();
            }
        });
    }
}

deleteDepModal = (email) => {
    if(email && email != ''){
            $('.preloader').show();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: "api/seller/get_last_deposit_not_conc",
                method: 'POST',
                data: {email: email},
                dataType: 'json',
                success: function (res){
                    if(res.success){
                        $('#deleteDepModal .modal-body').html(res.html);
                        $('#deleteDep').data('email', email);
                        $('#deleteDep').on('click', deleteDep);
                        $(".preloader").fadeOut();
                        $('#deleteDepModal').modal({backdrop: 'static', keyboard: false});
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

cleanFrom = () => {
    $('#cod').val('');
    $('#bankMod').val('');
    $('#date').val(retDate());
    $('#amount').val('');
    $('#userDep').text('');
    $('#reason_other').val('');
    $('#reason-content').attr('hidden', true);
}

opeAndFormat = function(val1, val2, ope){
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
}

$(document).ready( () => {
    var format = {
        autoclose: true,
        format: 'dd-mm-yyyy',
        todayHighlight: true,
        endDate: new Date(),
        startDate: new Date(new Date().setDate(new Date().getDate() - 2))
    };

    $('#date').datepicker(format);

    $.validator.methods.dateBank = function( value, element ) {
        return this.optional(element)||/^[0-3][0-9]-[0-1][0-9]-[0-9]{4}$/i.test(value);
    }

    //-----------------------------------------------------------------------//
    let rules = {
        bank:{
            required: true
        },
        date:{
            required: true,
            dateBank: true
        },
        amount:{
            required: true,
            digits: true
        }
    };

    let messages = {
        bank: {
            required: "Debe seleccionar un banco."
        },
        date: {
            required: "Debe seleccionar una fecha.",
            dateBank: "Debe escribir una fecha válida (dd-mm-yyyy)."
        },
        amount:{
            required: 'Debe escribir el monto depósitado.',
            digits: 'Debe escribir un monto válido.'
        }
    };

    let vf = $('#formDepositManual').validate({
        rules: rules,
        messages: messages
    });

    $('#manualLoad .close').on('click', function(e){
        cleanFrom();
    });

    //Evento para el selecto de banco en depósito manual
    $('#bankMod').on('change', function(e){
        let val = $(this).val();

        $('#reason_other').val('');
        vf.destroy();

        if(val == 'OTHER'){
            rules.reason_other = {
                required: true,
            };

            messages.reason_other = {
                required: "Debe escribir el motivo de depósito."
            };

            vf = $('#formDepositManual').validate({
                rules: rules,
                messages: messages
            });

            $('#reason-content').attr('hidden', null);
        }else{
            delete rules.reason_other;
            delete messages.reason_other;

            vf = $('#formDepositManual').validate({
                rules: rules,
                messages: messages
            });

            $('#reason-content').attr('hidden', true);
        }
    });

    $('#saveFormDeposit').on('click', function(e){
        if($('#formDepositManual').valid()){
            $('.preloader').show();

            var form = $('#formDepositManual').serialize();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: "api/seller/load_manual_deposit",
                method: 'POST',
                data: form,
                dataType: 'json',
                success: function (res) {
                    $(".preloader").fadeOut();
                    
                    if(res.success){
                        alert('Depósito insertado exitosamente.');

                        let table = $('#listuserDebt').DataTable();
                        let index = table.row('#'+$('#cod').val()).index();
                        let cell = table.cell({row:index, column:9});
                        let data = cell.data();

                        data.deposits = opeAndFormat($('#amount').val(), data.deposits, '+');
                        cell.data(data).draw();
                    }else{
                        alert(res.msg);
                    }
                    cleanFrom();
                    
                    $('#manualLoad .close').trigger('click');
                },
                error: function (res) {
                    alert('No se pudo crear el depósito.');
                    $(".preloader").fadeOut();
                }
            });
        }
    });

    //-----------------------------------------------------------------------//
    $('#deleteDepModal').on('hidden.bs.modal', function (event){
        $('#deleteDepModal .modal-body').html('');
        $(".preloader").fadeOut();
    });

    $('#deleteDepModal .close').on('click', function (event){
        $('#deleteDepModal .modal-body').html('');
    });

    //-----------------------------------------------------------------------//
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


    //-----------------------------------------------------------------------//
    //carga de depositos

    $('#loadDep').on('click', function(e){
        $('#deposits').trigger('click');
    });

    $('#deposits').on('change', function(e){
        $('.preloader').show();

        let form = $('#formDepositFile');

        if(form.length){
            let formData = new FormData(form[0]);
                url = form.attr('action');

            $('#list-da tbody').html('');
            $('#asigned-content').attr('hidden', true);

            //Borrar despues de la prueba
            if ($.fn.DataTable.isDataTable('#list-na')){
                $('#list-na').DataTable().destroy();
            }
            $('#pending-content').attr('hidden', true);
            //hasta aqui

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: url,
                method: 'POST',
                data: formData,
                mimeType: "multipart/form-data",
                processData: false,
                contentType: false,
                success: function (res) {
                    res = JSON.parse(res);

                    if(res.success){
                        if(res.OK){
                            let htmlt = '';
                            $.each(res.OK, function(i, item) {
                                htmlt += '<tr>';
                                htmlt += '<td>'+i+'</td>';
                                htmlt += '<td>'+item.user_name+'</td>';
                                htmlt += '<td>'+item.amount_txt+'</td>';
                                htmlt += '<td>'+item.n_dep+'</td>';
                                htmlt += '</tr>';
                            });

                            if(htmlt != ''){
                                $('#list-da tbody').html(htmlt);

                                $('#asigned-content').attr('hidden', null);
                            }

                            drawTable($('#org').val());
                        }

                        //Borrar esto despues de la prueba
                        /*if(res.notOK){
                            let htmlt = '';

                            $.each(res.notOK, function(i, item) {
                                htmlt += '<tr>';
                                htmlt += '<td>'
                                htmlt += '<button type="button" class="btn btn-info btn-md" style="width: 115px;">Asignar</button>';
                                htmlt += '<button type="button" class="btn btn-danger btn-md" style="width: 115px;">Eliminar</button>';
                                htmlt += '</td>';
                                htmlt += '<td>'+item.bank+'</td>';
                                htmlt += '<td>'+item.concepto+'</td>';
                                htmlt += '<td>'+item.amount_txt+'</td>';
                                htmlt += '<td>'+item.date_dep+'</td>';
                                htmlt += '<td>'+item.date_load+'</td>';
                                htmlt += '<td>'+item.reason+'</td>';
                                htmlt += '</tr>';
                            });

                            if(htmlt != ''){
                                $('#list-na tbody').html(htmlt);

                                $('#list-na').DataTable({searching: false, paging: true});

                                $('#pending-content').attr('hidden', null);
                            }
                        }*/
                        //Hasta aqui
                        
                        alert(res.msg);

                        loadDepositNotAsigned();
                    }else{
                        if(res.msg){
                            alert(res.msg);
                        }else{
                            alert('Ocurrio un error cargando el archivo.');
                        }
                    }

                    $('#deposits').val('');
                    $(".preloader").fadeOut();
                },
                error: function (res) {
                    $(".preloader").fadeOut();
                    alert('No se pudo procesar el archivo.');
                }
            });
        }
    });

    deleteDepNA = function(id){
        let conf = confirm("¿Seguro que deseas eliminar el depósito?");

        if(conf){
            $('.preloader').show();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: 'api/seller/delete_deposit_na',
                method: 'POST',
                data: {id: id},
                dataType: 'json',
                success: function (res) {
                    if(res.success){
                        alert('Depósito eliminado exitosamente.');
                        loadDepositNotAsigned();
                    }else{
                        $(".preloader").fadeOut();
                        alert('Ocurrio un error eliminando el depódito no asignado.');
                    }
                },
                error: function (res) {
                    $(".preloader").fadeOut();
                    alert('Ocurrio un error eliminando el depódito no asignado.');
                }
            });
        }
    }

    loadDepositNotAsigned = function(){
        $('.preloader').show();

        if ($.fn.DataTable.isDataTable('#list-na')){
            $('#list-na').DataTable().destroy();
        }

        $('#pending-content').attr('hidden', true);

        $.ajax({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            async: true,
            url: 'api/seller/load_deposit_na',
            method: 'POST',
            data: {},
            dataType: 'json',
            success: function (res) {
                if(res.success){
                    if(res.deposits){
                        let htmlt = '';

                        $.each(res.deposits, function(i, item) {
                            htmlt += '<tr>';
                            htmlt += '<td>'
                            htmlt += '<button type="button" class="btn btn-info btn-md" style="width: 115px;" onclick="assignDeposit(\''+item.id+'\')">Asignar</button>';
                            htmlt += '<button type="button" class="btn btn-danger btn-md" style="width: 115px;" onclick="deleteDepNA(\''+item.id+'\')">Eliminar</button>';
                            htmlt += '</td>';
                            htmlt += '<td>'+item.bank+'</td>';
                            htmlt += '<td>'+item.concepto+'</td>';
                            htmlt += '<td>'+item.amount_txt+'</td>';
                            htmlt += '<td>'+item.date_dep+'</td>';
                            htmlt += '<td>'+item.date_load+'</td>';
                            htmlt += '<td>'+item.reason+'</td>';
                            htmlt += '</tr>';
                        });

                        if(htmlt != ''){
                            $('#list-na tbody').html(htmlt);

                            $('#list-na').DataTable({searching: false, paging: true});

                            $('#pending-content').attr('hidden', null);
                        }
                    }
                }else{
                    if(res.msg){
                        alert(res.msg);
                    }else{
                        alert('Ocurrio un error cargando los depósitos no asignados.');
                    }
                }

                $(".preloader").fadeOut();
            },
            error: function (res) {
                $(".preloader").fadeOut();
                alert('Ocurrio un error cargando los depósitos no asignados.');
            }
        });
    }

    assignDeposit = function(id){
        $('#depAs').val(id);

        $('#manualAssign').modal({backdrop: 'static', keyboard: false});
    }

    $('#manualAssign').on('hidden.bs.modal', function (event) {
      $('#depAs').val('');
      //$('#userAS').val('');
    });

    $('#saveformDepositAssign').on('click', function(e){
        let url = $('#formDepositAssign').attr('action');

        if(url.trim() != ''){
            $('.preloader').show();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: url,
                method: 'POST',
                data: {id: $('#depAs').val(), user: $('#userAS').val()},
                dataType: 'json',
                success: function (res) {
                    if(res.success){
                        $('#manualAssign').modal('hide');
                        alert('Depósito asignado exitosamente.');
                        loadDepositNotAsigned();

                        let table = $('#listuserDebt').DataTable();
                        let index = table.row('#'+res.cod).index();
                        let cell = table.cell({row:index, column:9});
                        let data = cell.data();

                        data.deposits = opeAndFormat(res.amount, data.deposits, '+');
                        cell.data(data).draw();

                        //drawTable($('#org').val());
                    }else{
                        $(".preloader").fadeOut();
                        alert(res.msg);
                    }
                },
                error: function (res) {
                    $(".preloader").fadeOut();
                    alert('Ocurrio un error, No se pudo asignar el depósito.');
                }
            });
        }
    });

    //inicializando buscador de usuarios
    $('#userAS').selectize({
        valueField: 'email',
        labelField: 'username',
        searchField: 'username',
        options: [],
        create: false,
        persist: false,
        render: {
            option: function(item, escape) {
                return '<p>'+item.name+' '+item.last_name+'</p>';
            }
        },
        load: function(query, callback) {
            if (!query.length) return callback();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: 'view/reports/get_filter_users_sellers',
                type: 'POST',
                dataType: 'json',
                cache: false,
                data: {
                    name: query
                },
                error: function() {
                    callback();
                },
                success: function(res){
                    if(res.success)
                        callback(res.users);
                    else
                        callback();
                }
            });
        }
    });

    //Cargando depósitos no asignados
    //loadDepositNotAsigned();

    $('#concAll').on('click', function(e){
        if(!$(e.currentTarget).is(':checked'))
            $('input[name=conciliate]').prop('checked', null);
        else
            $('input[name=conciliate]').prop('checked', true);
    });

    //-----------------------------------------------------------------------//
    $('#conciliateBash').on('click', function(e){
        var users = [];

        $('input[name=conciliate]:checked').each(function(){
            users.push($(this).val());
        });

        if(users.length){
            $('.preloader').show();

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                async: true,
                url: "api/seller/bash_conciliate",
                method: 'POST',
                data: {users: users},
                dataType: 'json',
                success: function (res) {
                    if(res.success){
                        drawTable($('#org').val(), 'Pagos conciliados exitosamente.');
                    }else{
                        alert(res.msg);
                        $(".preloader").fadeOut();
                    }
                },
                error: function (res) {
                    alert('No se pudo hacer la conciliación.');
                    $(".preloader").fadeOut();
                }
            });
        }
    });
    $('#filter').on('click', function(e){
        drawTable($('#org').val());
    });
    $('#filter').trigger('click');
});