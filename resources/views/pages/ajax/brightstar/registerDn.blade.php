<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Asociar MSISDN</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Brighstar</a></li>
                <li class="active">Registrar MSISDN</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class="container">
                <div class="row">
                    <form class="form-horizontal" id="searchOrderForm" method="POST" action="">
                        {{ csrf_field() }}

                        <h3 class="box-title">Id de la orden</h3>

                        <div class="input-group">
                            <input type="text" class="form-control" id="order"  name="order" placeholder="ID de la orden">
                            <span class="input-group-btn">
                                <button class="btn btn-success" type="button" id="searchStatus">Buscar Orden</button>
                            </span>
                        </div>
                    </form>
                </div>
            </div>

            <hr>

            <div id="order-content">
    
            </div>

        </div>
    </div>
</div>

<script type="text/javascript">
    $(function () {
        saveOrderChange = function(e){
            $(e.currentTarget).attr('disabled',true);

            var order = $('#order').val().trim();

            if(order == ''){
                alert('Debe escribir el ID del cliente');
                $(e.currentTarget).attr('disabled',false);
            }else{
                if($('#saveOrderForm').valid()){
                    $('.preloader').show();
                    var data = $("#saveOrderForm").serialize()+'&order='+order;
                    $.ajax({
                        type: "POST",
                        url: "{{route('brightstar.processOrders')}}",
                        data: data,
                        dataType: "json",
                        success: function(response){
                            if(!response.error){
                                alert(response.message);
                                $('#order-content').html('');
                            }else{
                                alert(response.message);
                            }

                            $(e.currentTarget).attr('disabled',false);
                            $(".preloader").hide();
                        },
                        error: function(err){
                            $(e.currentTarget).attr('disabled',false);
                            $(".preloader").hide();
                        }
                    });
                }else{
                    $(e.currentTarget).attr('disabled',false);
                }
            }
        }

        $('#searchStatus').on('click', function(e){
            $('#searchStatus').attr('disabled',true);

            var order = $('#order').val().trim(),
                msg = false;
            if(order == ''){
                msg = 'Debe escribir el ID del cliente';
            }

            if(msg){
                $('#searchStatus').attr('disabled',false);
                alert(msg);
            }else{
                $('.preloader').show();

                var data = $("#searchOrderForm").serialize();

                $.ajax({
                    type: "POST",
                    url: "{{route('brightstar.getOrders')}}",
                    data: data,
                    dataType: "json",
                    success: function(response){
                        if(!response.error){
                            $('#order-content').html(response.html);
                            $('#save').bind('click', saveOrderChange);
                        }else{
                            alert(response.message);
                        }

                        $('#searchStatus').attr('disabled',false);
                        $(".preloader").hide();
                    },
                    error: function(err){
                        $('#searchStatus').attr('disabled',false);
                        $(".preloader").hide();
                    }
                });
            }
        });
    });
</script>