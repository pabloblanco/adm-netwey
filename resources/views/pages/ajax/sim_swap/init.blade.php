<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">SIM SWAP</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Inventario</a></li>
                <li class="active">SIM SWAP</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <section class="m-t-40">
                <div class="sttabs tabs-style-underline">
                    <nav>
                        <ul>
                            <li class="tab-current">
                                <a href="#section-iconbox-1" class="sticon ti-direction-alt">
                                    <span>Hacer SIM SWAP</span>
                                </a>
                            </li>
                            <li class="">
                                <a href="#section-iconbox-2" class="sticon ti-search">
                                    <span>Consultar SIM SWAP</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                    <div class="content-wrap">
                        <section id="section-iconbox-1" class="content-current">
                            <div class="row">
                                <form class="form-horizontal" id="searchDNForm" method="POST" action="">
                                    {{ csrf_field() }}

                                    <h3 class="box-title">MSISDN del Cliente</h3>

                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dno"  name="dno" placeholder="DN del cliente">
                                        <span class="input-group-btn">
                                            <button class="btn btn-success" type="button" id="searchDNOrg">Buscar DN</button>
                                        </span>
                                    </div>
                                </form>

                                <div class="col-md-12 p-t-30" id="step1"></div>
                            </div>
                        </section>
                        <section id="section-iconbox-2" class="">
                            <div class="row">
                                <form class="form-horizontal" id="statusSwapForm" method="POST" action="">
                                    {{ csrf_field() }}

                                    <h3 class="box-title">MSISDN del Cliente</h3>

                                    <div class="input-group">
                                        <input type="text" class="form-control" id="dnv"  name="dnv" placeholder="DN del cliente">
                                        <span class="input-group-btn">
                                            <button class="btn btn-success" type="button" id="searchStatus">Buscar DN</button>
                                        </span>
                                    </div>
                                </form>

                                <div class="col-md-12 p-t-30" id="verify"></div>
                            </div>
                        </section>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<script src="js/cbpFWTabs.js"></script>

<script type="text/javascript">
    (function() {
        var div = document.createElement("div");

        [].slice.call(document.querySelectorAll('.sttabs')).forEach(function(el) {
            new CBPFWTabs(el);
        });

        doSwap = function(e){
            var dnd = $('#dnDes').val().trim(),
                dno = $('#dno').val().trim(),
                imei = $('#imei').val().trim();

            //Validando que si es un sim-swap con modem, escriba el imei del equipo.
            if(imei == '' && $('#imei').is(':visible')){
                alert('Debe escribir el imei del model destino');
                return;
            }

            if(dnd && dno){
                swal("Por favor verificar la informacion para el SIM SWAP", {
                    //html:true,
                    content: div,
                    buttons: {
                        catch: {
                            text: "Hacer SIM SWAP",
                            value: "doSwap",
                        }
                    },
                })
                .then((value) => {
                    switch (value) {
                        case "doSwap":
                            $('.preloader').show();
                            var data = $("#swapForm").serialize()+'&dno='+dno;

                            $.ajax({
                                headers: {
                                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                                },
                                type: "POST",
                                url: "{{route('ClientController.simSwapStep2')}}",
                                data: data,
                                dataType: "json",
                                success: function(response){
                                    if(!response.error){
                                        $('#step1').html('');
                                        swal("OK!", "SIM SWAP realizado con exito.", "success");
                                    }else{
                                        alert(response.message);
                                    }
                                    
                                    $(".preloader").hide();
                                },
                                error: function(err){
                                    alert('Ocurrio un error, por favor intente mas tarde.');
                                    $(".preloader").hide();
                                }
                            });
                        break;
                    }
                });
            }else{
                alert('Faltan datos.')
            }
        }

        getProfile = function(e){
            var dnd = $('#dnDes').val().trim();

            if(dnd){
                $('.preloader').show();
                $('#des-content').hide();
                $('#error-simd').hide();
                $('#processSwap').attr('disabled', true);
                $('#imei').val('');

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    type: "POST",
                    url: "{{route('profile_altam')}}/"+dnd,
                    dataType: "json",
                    success: function(response){
                        if(response.status == "success"){
                            if(response.msisdn && !response.msisdn.redirect && !response.msisdn.supplementaryOffers && response.msisdn.status != 'active'){
                                $('#status').text('IDLE');

                                //Cargando div de info para confirmar sim swap
                                div.innerHTML = '<div class="col-md-6">'+
                                            '<h3 class="box-title">Origen</h3>'+
                                            '<dl>'+
                                                '<dt>MSISDN</dt>'+
                                                '<dd>'+$('#dno').val()+'</dd>'+
                                                '<dt>ICCID</dt>'+
                                                '<dd>'+$('#iccOrigen').text()+'</dd>'+
                                            '</dl>'+
                                        '</div>'+
                                        '<div class="col-md-6">'+
                                            '<h3 class="box-title">Destino</h3>'+
                                            '<dl>'+
                                                '<dt>MSISDN</dt>'+
                                                '<dd>'+dnd+'</dd>'+
                                                '<dt>ICCID</dt>'+
                                                '<dd>'+response.msisdn.ICCID+'</dd>'+
                                            '</dl>'+
                                        '</div>';

                                $('#processSwap').attr('disabled', null);
                            }else{
                                $('#error-simd').show();
                                $('#status').text(response.msisdn.status);
                            }

                            $('#iccid').text(response.msisdn.ICCID);

                            if(response.imei){
                                $('#imei').val(response.imei);
                            }

                            $('#des-content').show();
                        }else{
                            alert('Ocurrio un error consultando la servicialidad.');
                        }
                        $(".preloader").hide();
                    },
                    error: function(err){
                        alert('Ocurrio un error, por favor intente mas tarde.');
                        $(".preloader").hide();
                    }
                });
            }else{
                alert('Debe escribir un DN destino');
            }
        }

        checkType = function(){
            if($('[name=typeswap]:checked').val() == 'modem')
                $('#imei-content').show();
            else
                $('#imei-content').hide();
        }

        $('#searchDNOrg').on('click', function(e){
            if($('#dno').val().trim() != ''){
                var data = $("#searchDNForm").serialize();

                $('.preloader').show();

                $.ajax({
                    type: "POST",
                    url: "{{route('ClientController.simSwapStep1')}}",
                    data: data,
                    dataType: "json",
                    success: function(response){
                        if(!response.error){
                            $('#step1').html(response.html);
                            $('#btnProD').bind('click', getProfile);
                            $('#processSwap').bind('click', doSwap);
                            $('[name=typeswap]').bind('click', checkType);
                            checkType();
                        }else{
                            $('#step1').html('');
                            alert(response.message);
                        }

                        $(".preloader").hide();
                    },
                    error: function(err){
                        $('#step1').html('');
                        alert('Ocurrio un error, por favor intente mas tarde.');
                        $(".preloader").hide();
                    }
                });
            }else{
                $('#step1').html('');
                alert('Debe esribir un dn');
            }
        });

        $('#dno').on('focus', function(){
            $('#step1').html('');
        });

        $('#searchStatus').on('click', function(e){
            if($('#dnv').val().trim() != ''){
                var data = $("#statusSwapForm").serialize();

                $('.preloader').show();

                $.ajax({
                    type: "POST",
                    url: "{{route('ClientController.verifySwap')}}",
                    data: data,
                    dataType: "json",
                    success: function(response){
                        if(!response.error){
                            $('#verify').html(response.html);
                        }else{
                            $('#verify').html('');
                            alert(response.message);
                        }
                        $(".preloader").hide();
                    },
                    error: function(err){
                        $('#verify').html('');
                        alert('Ocurrio un error, por favor intente mas tarde.');
                        $(".preloader").hide();
                    }
                });
            }else{
                $('#verify').html('');
                alert('Debe esribir un dn');
            }
        });

        $('#dnv').on('focus', function(){
            $('#verify').html('');
        });

    })();
</script>