<style type="text/css">
    .selectize-input:after{
        content: none !important;
    }
</style>

<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Reporte de saldos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Reporte</a></li>
                <li class="active">Reporte de saldos</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <section class="m-t-40">
            <div class="row white-box">
                <div class="col-md-12">
                    <h3 class="text-center">
                        Reporte de saldos
                    </h3>
                </div>
                <div class="col-md-12 col-sm-12">
                    <form id="filterConc" name="filterConc" class="" method="POST">
                        <div class="col-md-3 col-sm-6">
                            <select id="userS" name="userS" class="form-control" placeholder="Seleccione un Coordinador" data-msg="Debe seleccionar un usuario." required style="width: 100% !important;">
                                <option value="">Seleccione un Coordinador</option>
                            </select>
                        </div>

                        <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <input type="text" name="dateb" id="dateb" class="form-control" placeholder="dd-mm-yyyy">
                        </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <input type="text" name="datee" id="datee" class="form-control" placeholder="dd-mm-yyyy">
                        </div>
                        </div>

                        <div class="col-md-3 col-sm-6">
                        <div class="form-group">
                            <button class="btn btn-success" id="filter" type="button">
                                Buscar
                            </button>
                        </div>
                        </div>
                    </form>
                </div>
                <div class="col-md-12 p-t-20" id="report-content" hidden>
                    <div class="card card-outline-primary text-center text-dark m-b-10">
                        <div class="card-block">
                            <header>Reporte de <span id="user"></span></header>
                            <hr>
                            <div class="row">
                                <div class="col-md-12 pull-left">
                                    <label class="pull-left">
                                        Saldo inicial (<span id="dateSI"></span>):
                                        $<span id="si"> </span>
                                    </label>
                                </div>
                                <div class="col-md-12 pull-left">
                                    <label class="pull-left">
                                        Dep&oacute;sitos (<span class="dateR"></span>):
                                        $<span id="dep"></span> <a href="#" class="detail" id="DepModalLink" {{-- data-toggle="modal" data-target="#DepModal" --}}> Ver detalle </a>
                                    </label>
                                </div>
                                <div class="col-md-12 pull-left">
                                    <label class="pull-left">
                                        Conciliado (<span class="dateR"></span>):
                                        $<span id="conc"></span> <a href="#" class="detail" id="detailModalLink" {{-- data-toggle="modal" data-target="#detailModal" --}}> Ver detalle </a>
                                    </label>
                                </div>
                                <div class="col-md-12">
                                    <label class="pull-left">
                                        Saldo final (<span id="dateSF"></span>):
                                        $<span id="sf"></span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</div>

<div class="modal modalAnimate" id="DepModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#DepModal">&times;</button>
                <h4 class="modal-title"> Detalle de desp&oacute;sitos</h4>
            </div>

            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">

            </div>
        </div>
    </div>
</div>

<div class="modal modalAnimate" id="detailModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#detailModal">&times;</button>
                <h4 class="modal-title">Detalle de conciliaciones</h4>
            </div>

            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">

            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function () {
        /*function clearForm(){
            if($('#userS').selectize())
                $('#userS').selectize()[0].selectize.clear();

            $('#cod').val('');
        }*/

        var config = {
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true,
            endDate: new Date()
        }

        $('#dateb').datepicker(config);

        $('#datee').datepicker(config);

        var configSelect = {
            valueField: 'email',
            labelField: 'username',
            searchField: 'username',
            options: [],
            create: false,
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
                    url: '{{route("get_user_by_deposit")}}',
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
                            callback(res.users);
                        else
                            callback();
                    }
                });
            }
        };

        $('#userS').selectize(configSelect);

        $.validator.methods.dateBank = function( value, element ) {
            return this.optional(element)||/^[0-3][0-9]-[0-1][0-9]-[0-9]{4}$/i.test(value);
        }

        $('#filterConc').validate({
            rules: {
                userS: {
                    required: true
                },
                dateb: {
                    //required: true,
                    dateBank: true
                },
                datee: {
                    //required: true,
                    dateBank: true
                }
            },
            messages: {
                userS: {
                    required: "Debe seleccionar un usuario."
                },
                dateb: {
                    //required: "Debe seleccionar una fecha inicio.",
                    dateBank: "Debe escribir una fecha válida (dd-mm-yyyy)."
                },
                datee: {
                    //required: "Debe seleccionar una fecha fin.",
                    dateBank: "Debe escribir una fecha válida (dd-mm-yyyy)."
                }
            }
        });

        $('#filter').on('click', function(e){
            $('#report-content').attr('hidden', true);
            if($('#filterConc').valid()){
                $(".preloader").fadeIn();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '{{route("getReportConc")}}',
                    type: 'POST',
                    dataType: 'json',
                    data: $('#filterConc').serialize(),
                    error: function() {
                        alert('No se pudo obtener el reporte.');
                        $(".preloader").fadeOut();
                    },
                    success: function(res){
                        if(!res.error){
                            $('#dateSI').text(res.dateb);
                            $('.dateR').text(res.dateb+' a '+res.datee);
                            $('#dateSF').text(res.datee);
                            $('#si').text(res.ini);
                            $('#sf').text(res.final);
                            $('#conc').text(res.conc);
                            $('#dep').text(res.dep);
                            $('#user').text(res.usuario);
                            $('.detail').data('user', res.email);
                            $('.detail').data('dateb', res.dateb);
                            $('.detail').data('datee', res.datee);

                            $('#report-content').attr('hidden', null);
                        }
                        else
                            alert(res.msg);

                        $(".preloader").fadeOut();
                    }
                });
            }
        });

        sales = function(e){
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

        $('#detailModalLink').on('click',function(){
            $('#detailModal').modal({backdrop: 'static', keyboard: false});
        });

        $('#detailModal').on('show.bs.modal', function (event){
            // var button = $(event.relatedTarget),
            //     email = button.data('user'),
            //     dateb = button.data('dateb'),
            //     datee = button.data('datee');

            var    email = $('#userS').val(),
                dateb = $('#dateSI').text(),
                datee = $('#dateSF').text();

            // console.log(email);
            // console.log(dateb);
            // console.log(datee);

            if(email && email != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('getDetailConc')}}",
                    method: 'POST',
                    data: {email: email, dateb: dateb, datee: datee},
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();
                        if(res.success){
                            $('#detailModal .modal-body').html(res.html);
                            $('.seeSales').bind('click', sales);
                        }else{
                            $('#detailModal .close').trigger('click');
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        alert('No se pudo cargar el detalle de las conciliaciones.');
                        $(".preloader").fadeOut();
                    }
                });
            }
        });

        $('#detailModal').on('hidden.bs.modal', function (event){
            $('#detailModal .modal-body').html('');
            $(".preloader").fadeOut();
        });


        $('#DepModalLink').on('click',function(){
            $('#DepModal').modal({backdrop: 'static', keyboard: false});
        });

        $('#DepModal').on('show.bs.modal', function (event){
            //var button = $(event.relatedTarget),
            var    email = $('#userS').val(),
                dateb = $('#dateSI').text(),
                datee = $('#dateSF').text();

            // console.log(email);
            // console.log(dateb);
            // console.log(datee);

            if(email && email != ''){
                $('.preloader').show();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('getDetailDeposits')}}",
                    method: 'POST',
                    data: {email: email, dateb: dateb, datee: datee},
                    dataType: 'json',
                    success: function (res) {
                        $(".preloader").fadeOut();

                        if(res.success){
                            $('#DepModal .modal-body').html(res.html);
                        }else{
                            alert(res.msg);
                        }
                    },
                    error: function (res) {
                        alert('No se pudo cargar los últimas depósitos del usuario.');
                        $(".preloader").fadeOut();
                    }
                });
            }
        });

        $('#DepModal .close').on('click', function (event){
            $('#DepModal .modal-body').html('');
        });

    });
</script>
<script src="js/common-modals.js"></script>