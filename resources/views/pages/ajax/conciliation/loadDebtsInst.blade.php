<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Conciliaci&oacute;n de ventas en abono</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Vendedores</a></li>
                <li class="active">Conciliaci&oacute;n efectivo abonos</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="white-box">
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center p-b-20">
                    Conciliar deuda de venta en abono
                </h3>

                <div class="col-md-12 p-t-20">
                    <div class="col-md-5 col-sm-12">
                        <div class="form-group">
                            <select id="coordinador_pay" name="coordinador_pay" class="form-control">
                          <option value="">Seleccione un usuario</option>
                        </select>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <button class="btn btn-success" id="filter" type="button">
                            Filtrar
                        </button>
                    </div>
                </div>

                <div class="col-md-12 p-t-20 user-load" hidden>
                    <button class="btn btn-success" id="filter" type="button" data-toggle="modal" data-target="#manualLoad">
                        Cargar dep&oacute;stio
                    </button>
                </div>

                <div class="col-md-12 p-t-20 user-active-dep" hidden>
                    <div class="col-md-12">
                        <label class="pull-left">
                            <b>&Uacute;ltimo Dep&oacute;sito cargado</b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Banco: <b id="bank_dep_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            monto: <b id="amount_dep_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            fecha: <b id="date_dep_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <a href="#" id="delete-dep" style="color: #ff0000;">Eliminar</a>
                    </div>
                </div>

                <div class="col-md-12 p-t-20 user-load" hidden>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Usuario: <b id="user_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Correo: <b id="email_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Deuda de ventas en Abono: <b id="debt_d"></b>
                        </label>
                    </div>
                    <div class="col-md-12">
                        <label class="pull-left">
                            Saldo a favor: <b>$<span id="resd_d"></span></b>
                        </label>
                    </div>
                </div>
            </div>
        </div>
        <div class="row user-load" hidden>
            <div class="col-md-12 p-t-20">
                <div class="table-responsive">
                    <table id="listDebtUser" class="table table-striped">
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>msisdn</th>
                                <th>pack</th>
                                <th>service</th>
                                <th>art&iacute;culo</th>
                                <th>Cuota</th>
                                <th>Fecha recepción</th>
                                <th>Monto</th>
                                <th>
                                    <div> 
                                        <input type="checkbox" name="concAll" id="concAll"> 
                                    </div>
                                    Conciliar
                                </th>
                            </tr>
                        </thead>

                        <tfoot>
                            <tr class="group">
                                <th colspan="8">
                                    <div class="pull-right">
                                        <button class="btn btn-success" id="conciliateBash" type="button">
                                            Conciliar
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="manualLoad" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Carga de dep&oacute;sito manual</h4>
            </div>

            <div class="modal-body">
                <h5 class="p-b-10">Dep&oacute;sito para: <b id="userDep"> </b> </h5>
                <form id="formDepositManual" name="formDepositManual" method="POST">
                    <input type="hidden" name="cod" id="cod">
                    <div class="form-group">
                        <select id="bankMod" name="bankMod" class="form-control" required>
                            <option value="" selected>Seleccione un banco</option>
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <input type="text" name="date" id="date" class="form-control" placeholder="dd-mm-yyyy" value="{{date('d-m-Y')}}">
                    </div>

                    <div class="form-group">
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="0">
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" id="saveFormDeposit" class="btn btn-success">Insertar</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="lastConcModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">&Uacute;ltimas 10 Conciliaciones</h4>
            </div>

            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
                
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function (){
        var configSelect = {
            valueField: 'email',
            labelField: 'name',
            searchField: ['name', 'last_name'],
            options: [],
            create: false,
            render: {
                option: function(item, escape) {
                    return '<p>'+escape(item.name)+' '+escape(item.last_name)+'</p>';
                },
                item: function(item, escape) {
                    return '<spam>'+escape(item.name)+' '+escape(item.last_name)+'</spam>';
                }
            },
            load: function(query, callback) {
                if (!query.length) return callback();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: 'api/seller/get_users_deb',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        q: query
                    },
                    error: function() {
                        callback();
                    },
                    success: function(res){
                        if(res.success)
                            callback(res.clients);
                        else
                            callback();
                    }
                });
            }
        };

        $('#coordinador_pay').selectize(configSelect);

        formatMoney = function(number, decPlaces, decSep, thouSep) {
            decPlaces = isNaN(decPlaces = Math.abs(decPlaces)) ? 2 : decPlaces,
            decSep = typeof decSep === "undefined" ? "." : decSep;
            thouSep = typeof thouSep === "undefined" ? "," : thouSep;
            var sign = number < 0 ? "-" : "";
            var i = String(parseInt(number = Math.abs(Number(number) || 0).toFixed(decPlaces)));
            var j = (j = i.length) > 3 ? j % 3 : 0;

            return sign +
            (j ? i.substr(0, j) + thouSep : "") +
            i.substr(j).replace(/(\decSep{3})(?=\decSep)/g, "$1" + thouSep) +
            (decPlaces ? decSep + Math.abs(number - i).toFixed(decPlaces).slice(2) : "");
        }

        cleanFrom = function(){
            $('#cod').val('');
            $('#bankMod').val('');
            $('#date').val('{{date('d-m-Y')}}');
            $('#amount').val('');
            $('#userDep').text('');
        }

        $('#saveFormDeposit').on('click', function(e){
            if($('#formDepositManual').valid()){
                $('.preloader').show();

                var form = $('#formDepositManual').serialize();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('load_manual_deposit')}}",
                    method: 'POST',
                    data: form,
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();
                        if(res.success){
                            $('#manualLoad .close').trigger('click');
                            alert('Depósito insertado exitosamente.');
                            getInfoUser(false);
                        }else{
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        alert('No se pudo crear el depósito.');
                        $(".preloader").fadeOut();
                    }
                });
            }
        });

        drawTable = function(){
            $('.user-load').attr('hidden', true);

            if ($.fn.DataTable.isDataTable('#listDebtUser'))
                $('#listDebtUser').DataTable().destroy();

            var coord = $('#coordinador_pay').val();

            if(coord && coord.trim() != ''){
                $('.preloader').show();

                $('#listDebtUser').DataTable({
                    language: {
                        sProcessing:     "Procesando...",
                        sLengthMenu:     "Mostrar _MENU_ registros",
                        sZeroRecords:    "No se encontraron resultados",
                        sEmptyTable:     "Ningún dato disponible en esta tabla",
                        sInfo:           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                        sInfoEmpty:      "Mostrando registros del 0 al 0 de un total de 0 registros",
                        sInfoFiltered:   "(filtrado de un total de _MAX_ registros)",
                        sInfoPostFix:    "",
                        sSearch:         "Buscar:",
                        sUrl:            "",
                        sInfoThousands:  ",",
                        sLoadingRecords: "Cargando...",
                        oPaginate: {
                            sFirst:    "Primero",
                            sLast:     "Último",
                            sNext:     "Siguiente",
                            sPrevious: "Anterior"
                        },
                        oAria: {
                            sSortAscending:  ": Activar para ordenar la columna de manera ascendente",
                            sSortDescending: ": Activar para ordenar la columna de manera descendente"
                        }
                    },
                    searching: true,
                    processing: true,
                    serverSide: true,
                    paging: false,
                    ajax: {
                        url: "{{route('get_debt_inst_dt')}}",
                        data: function (d) {
                            d._token = $('meta[name="csrf-token"]').attr('content');
                            d.user = coord;
                        },
                        type: "POST"
                    },
                    initComplete: function(settings, json){
                        $('#concAll').prop('checked', null);
                        $('.user-load').attr('hidden', null);
                        $('.ch-con').bind('click', checkConc);
                        $(".preloader").fadeOut();
                    },
                    deferRender: true,
                    order: [[ 6, "desc" ]],
                    columns: [
                        {data: 'client'},
                        {data: 'msisdn'},
                        {data: 'pack'},
                        {data: 'service'},
                        {data: 'artic', searchable: false},
                        {data: 'quote', searchable: false},
                        {data: 'date', searchable: false},
                        {data: 'amount', searchable: false},
                        {
                            data: null,
                            render: function(data, type, row, meta){
                                var html = '<input type="checkbox" class="ch-con" name="conciliate" data-amount="'+row.real_amount+'" value="'+row.unique_transaction+'" data-pos="'+row.timestamp+'">';
                                return html;
                            },
                            searchable: false,
                            orderable: false
                        }
                    ]
                });
            }else{
                alert('Debe seleccionar un usuario.');
            }
        }

        $('#filter').on('click', function(e){
            cleanFrom();
            getInfoUser(true);
        });

        checkConc = function(e){
            var amount = parseFloat($(e.currentTarget).data('amount'));

            if($(e.currentTarget).is(':checked')){
                if(!validAmount('diff', amount)){
                    $(e.currentTarget).prop('checked', false);

                    alert('No se pueden conciliar mas ventas, se agoto el saldo a favor del usuario.');
                }
            }else{
                validAmount('sum', amount);
            }
        }

        $('#concAll').on('click', function(e){
            if(!$(e.currentTarget).is(':checked')){
                $('input[name=conciliate]').each(function(e){
                    if($(this).is(':checked')){
                        var amount = parseFloat($(this).data('amount'));
                        validAmount('sum', amount);
                        $(this).prop('checked', false);
                    }
                });
            }else{
                var sortEle = $('input[name=conciliate]');
                var ban = 0;

                sortEle.sort(function(a, b) {
                    return $(b).data('pos') < $(a).data('pos') ? 1 : -1;
                });

                sortEle.each(function(){
                    var amount = parseFloat($(this).data('amount'));

                    if(!validAmount('diff', amount)){
                        $(this).prop('checked', false);
                    }
                    else{
                        ban = 1;
                        $(this).prop('checked', true);
                    }
                });

                if(!ban){
                    alert('El saldo a favor no alcansa para conciliar ninguna cuota');
                    $(e.currentTarget).prop('checked', false);
                }
            }
        });

        validAmount = function(ope, amountch){
            var amount = parseFloat($('#resd_d').data('amount'));

            if(ope == 'diff'){
                var total = amount - amountch;

                if(total >= 0){
                    $('#resd_d').text(formatMoney(total, 2, ".", ","));

                    $('#resd_d').data('amount', total);

                    return true;
                }

                return false;
            }else{
                var total = amount + amountch;

                $('#resd_d').text(formatMoney(total, 2, ".", ","));

                $('#resd_d').data('amount', total);

                return true;
            }
        }

        $('#delete-dep').on('click', function(e){
            var coord = $('#coordinador_pay').val();

            if(coord && coord.trim() != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('delete_last_deposit')}}",
                    method: 'POST',
                    data: {email: coord},
                    dataType: 'json',
                    success: function (res) {
                        if(res.success){
                            alert('Depósito eliminado exitosamente.');
                            getInfoUser(false);
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
            }else{
                alert('Debe seleccionar un usuario.');
            }
        });

        getInfoUser = function(dt = true){
            var coord = $('#coordinador_pay').val();

            if(coord && coord.trim() != ''){
                $(".preloader").fadeIn();
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('get_info_user')}}",
                    method: 'POST',
                    data: {user: coord},
                    dataType: 'json',
                    success: function (res) {
                        if(res.success){
                            $('#userDep').text(res.data.name+' '+res.data.last_name);
                            $('#user_d').html(res.data.name+' '+res.data.last_name+' (<a href="#" data-email="'+res.data.email+'" data-toggle="modal" data-target="#lastConcModal">Ver últimas conciliaciones</a>)');
                            $('#email_d').text(res.data.email);
                            $('#debt_d').text(res.data.amountT);
                            $('#resd_d').text(res.data.amountR);
                            $('#resd_d').data('amount', res.data.amountRR);
                            $('#cod').val(res.data.cod);

                            if(res.data.isdepnc){
                                $('.user-active-dep').attr('hidden', null);
                                $('#bank_dep_d').text(res.data.dep_bank);
                                $('#amount_dep_d').text(res.data.dep_amount);
                                $('#date_dep_d').text(res.data.dep_date);
                            }else{
                                $('.user-active-dep').attr('hidden', true);
                                $('#bank_dep_d').text('');
                                $('#amount_dep_d').text('');
                                $('#date_dep_d').text('');
                            }

                            if(dt)
                                drawTable();
                            else
                                $(".preloader").fadeOut();
                        }else{
                            alert('no se pudo cargar los datos del usuario seleccionado.');
                        }
                    },
                    error: function (res) {
                        alert('no se pudo cargar los datos del usuario seleccionado.');
                        $(".preloader").fadeOut();
                    }
                });
            }else{
                alert('Debe seleccionar un usuario.');
            }
        }

        $('#conciliateBash').on('click', function(e){
            var coord = $('#coordinador_pay').val();

            if(coord && coord.trim() != ''){
                var cons = [];

                $('input[name=conciliate]:checked').each(function(){
                    cons.push($(this).val());
                });

                if(cons.length){
                    $('.preloader').show();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('bash_conciliate_ins')}}",
                        method: 'POST',
                        data: {cons: cons, user: coord},
                        dataType: 'json',
                        success: function(res){
                            if(res.success){
                                alert(res.msg);
                                getInfoUser(true);
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
            }else{
                alert('Debe seleccionar un usuario.');
            }
        });

        $('#lastConcModal').on('show.bs.modal', function (event){
            var coord = $('#coordinador_pay').val();

            if(coord && coord.trim() != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('get_conc_inst')}}",
                    method: 'POST',
                    data: {user: coord},
                    dataType: 'json',
                    success: function(res){
                        $(".preloader").fadeOut();

                        if(res.success){
                            $('#lastConcModal .modal-body').html(res.html);
                        }else{
                            $('#lastConcModal').modal('hide');
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        $('#lastConcModal').modal('hide');
                        alert('No se pudo cargar los últimas depósitos del usuario.');
                        $(".preloader").fadeOut();
                    }
                });
            }else{
                $('#lastConcModal').modal('hide');
                alert('Debe seleccionar un usuario.');
            }
        });
    });
</script>