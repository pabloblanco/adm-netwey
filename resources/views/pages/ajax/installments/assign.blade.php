<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Asignaci&oacute;n de m&oacute;dems</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Pago en Abonos</a></li>
                <li class="active">Asignaci&oacute;n de m&oacute;dems</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
        <section class="m-t-40">
            <div class="white-box">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="text-center">
                            Asignaci&oacute;n de m&oacute;dems
                        </h3>
                    </div>
                    <div class="col-md-12 p-t-20">
                        <div class="col-md-5 col-sm-12">
                            <div class="form-group">
                                <select id="findCoord" name="findCoord" class="form-control">
                                  <option value="">Seleccione un coordinador</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <button type="button" class="btn btn-success" id="load">Buscar</button>
                        </div>
                    </div>
                </div>

                <div class="row" id="sales-content" hidden></div>

            </div>
        </section>
</div>

<script type="text/javascript">
    $(document).ready(function (){
        var configSelect = {
                valueField: 'email',
                labelField: 'name',
                searchField: ['email'],
                options: [],
                create: false,
                render: {
                    item: function(item, escape) {
                        return '<spam>'+escape(item.name)+' '+escape(item.last_name)+'</spam>';
                    },
                    option: function(item, escape) {
                        return '<p>'+escape(item.name)+' '+escape(item.last_name)+'</p>';
                    }
                },
                load: function(query, callback) {
                    if (!query.length) return callback();

                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        url: '{{ route("installments.findUser") }}',
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
                                callback(res.coords);
                            else
                                callback();
                        }
                    });
                },
                onFocus: function(){
                    $('#sales-content').attr('hidden', true);
                    $('#sales-content').html('');
                }
            };

        $('#findCoord').selectize(configSelect);

        $('#load').on('click', function(e){
            var coord = $('#findCoord').val();
            if(coord && coord != ''){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('installments.consultCoordinador')}}",
                    method: 'POST',
                    data: {coord: coord},
                    success: function (res){
                        if(res.success){
                            $('#sales-content').html(res.html);
                            $('#assign').bind('click', assign);
                            $('#sales-content').attr('hidden', null);
                        }
                        else
                            alert(res.msg);
                    },
                    error: function (res) {
                        alert('No se pudo Consultar el coordinador.');
                    }
                });
            }
        });

        function assign(e){
            var t = $(e.currentTarget).data('total'),
                coord = $('#findCoord').val();

            if(t && t > 0 && coord && coord != ''){
                var a = $('#tokens').val();
                if(a != '' && a <= t){
                    $.ajax({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        async: true,
                        url: "{{route('installments.assignedCoord')}}",
                        method: 'POST',
                        data: {coordinador: coord, quantity: a},
                        success: function (res){
                            if(res.success){
                                $('#m-assign').text(a);
                                alert('Módems asignados.');
                            }
                            else
                                alert('No se pudieron asignar los módems.');
                        },
                        error: function (res) {
                            alert('No se pudieron asignar los módems.');
                        }
                    });
                }else{
                    alert('Puede asignar máximo '+t+' módem(s).')
                }
            }else{
                alert('No puede asignar módems a este coordiandor.');
            }
        }
        
    });
</script>