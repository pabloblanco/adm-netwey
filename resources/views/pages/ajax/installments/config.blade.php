<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Configuraci&oacute;n</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Pago en Abonos</a></li>
                <li class="active">Configuraci&oacute;n</li>
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
                        Configuraci&oacute;n de pago en abono
                    </h3>
                </div>
                
                <form id="configForm" method="POST">
                    <div class="col-md-6 col-sm-12 p-t-20">
                        <div class="form-group">
                            <label>Número de cuotas</label>
                            <input type="number" name="nquotes" id="nquotes" class="form-control" placeholder="20" min="2" value="{{ !empty($config) ? $config->quotes : ''}}">
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12 p-t-20">
                        <div class="form-group">
                            <label>Procentaje m&iacute;nimo de pago de la primera cuota</label>
                            <input type="number" name="pquotes" id="pquotes" class="form-control" placeholder="50" min="50" value="{{ !empty($config) ? $config->firts_pay : ''}}">
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Procentaje para el c&aacute;lculo de módems en abono</label>
                            <input type="number" name="assign" id="assign" class="form-control" placeholder="20" value="{{ !empty($config) ? $config->percentage : ''}}">
                            <div class="alert alert-success m-t-10" style="padding: 5px;">
                                El porcentaje se tomara en cuenta para el calculo del día 
                                <b>{{ date('d/m/Y', strtotime("next ".$days[!empty($config) ? $config->end_day - 1 : 0])) }}</b>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="form-group">
                            <label>Modem en deuda vencida permitidos (Coordinador)</label>
                            <input type="number" name="qpc" id="qpc" class="form-control" placeholder="2" min="1" value="{{ !empty($config) ? $config->m_permit_c : ''}}">
                        </div>
                    </div>

                    <div class="col-md-12 p-t-20">
                        <button type="button" class="btn btn-success" id="save">Guardar</button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function (){
        function validNumber(n){
            return (n && n != '' && parseInt(n) != 'NaN' && (n % 1) == 0);
        }

        $('#save').on('click', function(e){
            var n = $('#assign').val(),
                nq = $('#nquotes').val(),
                qpc = $('#qpc').val(),
                pq = $('#pquotes').val();

            if(validNumber(n) && validNumber(nq) && nq >= 2 && validNumber(pq) && pq >= 50 && validNumber(qpc) && qpc >= 1){
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    async: true,
                    url: "{{route('installments.configSave')}}",
                    method: 'POST',
                    data: {porc: n, numberq: nq, porcq: pq, qpc: qpc},
                    success: function (res) {
                        if(res.success)
                            alert('Configuración guardada exitosamente.');
                        else
                            alert('No se pudo guardar la configuración.');
                    },
                    error: function (res) {
                        alert('No se pudo guardar la configuración.');
                    }
                });
            }else{
                if(!validNumber(n))
                    alert('Error, porcentaje para cálculo de módems no válido');
                if(!(validNumber(nq) && nq >= 2))
                    alert('Error, número de cuotas no válido, debe ser entero y mayor o igual a 2');
                if(!(validNumber(pq) && pq >= 50))
                    alert('Error, el porcentaje para la primera cuota debe ser entero y mayor o igual a 50');
                if(!(validNumber(qpc) && qpc >= 1))
                    alert('Error, el maximo de modems permitido para deduda debe ser entero y mayor o igual a 1');
            }
        });
    });
</script>