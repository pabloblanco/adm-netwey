<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Deuda de coordinadores</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="#">Vendedores</a></li>
                <li class="active">Deuda de coordinadores</li>
            </ol>
        </div>
    </div>
</div>

<div class="container">
    <div class="white-box">
        <div class="row">
            <div class="col-md-12">
                <h3 class="text-center p-b-20">
                    Deuda de coordinadores
                </h3>

                {{--<form id="formFilter" name="formFilter" class="form-inline">
                    <div class="form-group p-r-20">
                        <select id="org" name="org" class="form-control" placeholder="Seleccione un Coordinador" data-msg="Debe seleccionar una organizacion." required>
                            @if(session('user')->profile->type == "master")
                            <option value="" selected>Seleccione un Organizaci&oacute;n</option>
                            @endif
                            @foreach($orgs as $org)
                                <option value="{{$org->id}}">{{$org->business_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <button class="btn btn-success" id="filter" type="button">
                            Filtrar
                        </button>
                    </div>
                </form>--}}
            </div>
        </div>
        <div class="row">
            <div class="col-md-12 p-t-20">
                <div class="table-responsive">
                    <table id="listuserDebt" class="table table-striped">
                        <thead>
                            <tr>
                                {{--<th>Opciones</th>--}}
                                <th>Usuario</th>
                                <th>Cod. Dep&oacute;sito</th>
                                <th>Saldo</th>
                                <th>Deuda Coord (Antigua)</th>
                                <th>D&iacute;as de deuda Coord</th>
                                <th>Deuda Coord (Hoy)</th>
                                <th>Deuda Vendedores (Antigua)</th>
                                <th>Deuda Vendedores (Hoy)</th>
                                <th>Deuda Abono</th>
                                <th>Monto</th>
                                <th>&Uacute;ltima Conciliaci&oacute;n</th>
                                {{--<th>
                                    <div>
                                        <input type="checkbox" name="concAll" id="concAll">
                                    </div>
                                    Conciliar
                                </th>--}}
                            </tr>
                        </thead>

                        {{--<tfoot>
                            <tr class="group">
                                <th colspan="10">
                                    <div class="pull-right">
                                        <button class="btn btn-success" id="conciliateBash" type="button">
                                            Conciliar
                                        </button>
                                    </div>
                                </th>
                            </tr>
                        </tfoot>--}}
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{--<div class="modal modalAnimate" id="manualLoad" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#manualLoad">&times;</button>
                <h4 class="modal-title">Carga de dep&oacute;sito manual</h4>
            </div>
            <div class="modal-body">
                <h5 class="p-b-10">Dep&oacute;sito para: <b id="userDep"> uuuu </b> </h5>
                <form id="formDepositManual" name="formDepositManual" method="POST">
                    <input type="hidden" name="cod" id="cod">
                    <div class="form-group">
                        <select id="bankMod" name="bankMod" class="form-control" required>
                            <option value="" selected>Seleccione un banco</option>
                            @foreach($banks as $bank)
                                <option value="{{$bank->id}}">{{$bank->name}}({{ substr($bank->numAcount, -4) }})</option>
                            @endforeach
                            <option value="OTHER">Otro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <input type="text" name="date" id="date" class="form-control" placeholder="dd-mm-yyyy" value="{{date('d-m-Y')}}">
                    </div>
                    <div class="form-group">
                        <input type="text" name="amount" id="amount" class="form-control" placeholder="0">
                    </div>
                    <div class="form-group" hidden="true" id="reason-content">
                        <input type="text" name="reason_other" id="reason_other" class="form-control" placeholder="Por favor ingrese el motivo del depósito">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveFormDeposit" class="btn btn-success">Insertar</button>
            </div>
        </div>
    </div>
</div>--}}

{{--<div class="modal modalAnimate" id="deleteDepModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                 <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#deleteDepModal">&times;</button>
                <h4 class="modal-title">Eliminar dep&oacute;sito</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
            </div>
        </div>
    </div>
</div>--}}

<div class="modal modalAnimate" id="detailModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                 <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#detailModal">&times;</button>
                <h4 class="modal-title">Detalle de deuda</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
            </div>
        </div>
    </div>
</div>

<div class="modal modalAnimate" id="detailModalSellers" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#detailModalSellers">&times;</button>
                <h4 class="modal-title">Detalle de deuda</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
            </div>
        </div>
    </div>
</div>

<div class="modal modalAnimate" id="detailModalInst" tabindex="-1" role="dialog" >
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                 <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#detailModalInst">&times;</button>
                <h4 class="modal-title">Detalle de deuda (Abono)</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
            </div>
        </div>
    </div>
</div>

<div class="modal modalAnimate" id="lastDepModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#lastDepModal">&times;</button>
                <h4 class="modal-title">&Uacute;ltimas 5 Conciliaciones</h4>
            </div>
            <div class="modal-body" style="overflow-y: auto; max-height: calc(100vh - 130px);">
            </div>
        </div>
    </div>
</div>

{{--<div class="modal modalAnimate" id="manualAssign" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="modal_close_btn close" id="modal_close_x" data-modal="#manualAssign">&times;</button>
                <h4 class="modal-title">Asignar dep&oacute;sito</h4>
            </div>
            <div class="modal-body">
                <h5 class="p-b-10">Por favor selecciona el usuario al que le quieres asociar el depósito</h5>
                <form id="formDepositAssign" name="formDepositAssign" action="{{ route('asociate_deposit_na') }}" method="POST">
                    <input type="hidden" name="depAs" id="depAs">
                    <div class="form-group">
                        <select id="userAS" name="userAS" class="form-control">
                            <option value="">Seleccione un usuario</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" id="saveformDepositAssign" class="btn btn-success">Asociar</button>
            </div>
        </div>
    </div>
</div>--}}

<script type="text/javascript">
    //retDate = () => {return '{{date('d-m-Y')}}';};
    //userStatus = () => {return 'A';};
</script>

<script src="js/loaddeposit/debtCoord.js?{{time()}}" defer="defer"></script>
<script src="js/common-modals.js"></script>