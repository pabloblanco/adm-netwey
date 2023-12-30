@php
  $accessPermission = 0;
  $addPermission = 0;
  $editPermission = 0;
  $delPermission = 0;
  foreach (session('user')->policies as $policy) {
    if ($policy->code == 'LDD-RLD')
      $accessPermission = $policy->value;
    if ($policy->code == 'LDD-CLD')
      $addPermission = $policy->value;
    if ($policy->code == 'LDD-ULD')
      $editPermission = $policy->value;
    if ($policy->code == 'LDD-DLD')
      $delPermission = $policy->value;
  }
@endphp

@if ($accessPermission > 0)
<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Lista de Descuentos</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li>
                    <a href="/islim/">
                        Dashboard
                    </a>
                </li>
                <li class="active">
                    Lista de Descuentos
                </li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            @if($addPermission > 0)
            <button class="btn btn-info btn-lg" data-target="#myModal" data-toggle="modal" id="open_modal_btn" type="button">
                Agregar
            </button>
            @endif
            <hr>
                <div class="row white-box">
                    <div class="table-responsive">
                        <table class="table table-striped" id="myTable">
                            <thead>
                                <tr>
                                    @if ($editPermission)
                                    <th>Acciones</th>
                                    @endif
                                    <th>Nombre</th>
                                    <th>Duracion</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($promos as $promo)
                                <tr>
                                    @if ($editPermission || $delPermission)
                                    <th class="row">
                                        @if($editPermission > 0)
                                        <button class="btn btn-warning btn-md button" style="max-width: 7vw;" onclick="update('{{ $promo }}')" type="button">
                                            Editar
                                        </button>
                                        @endif
                                    </th>
                                    @endif
                                    <th>{{ $promo->name }}</th>
                                    <th>
                                    @if($promo->lifetime != null)
                                        {{ $promo->lifetime }} Días
                                    @else
                                        N/A
                                    @endif
                                    </th>
                                    <th>
                                    @if ($promo->status == 'A')
                                        Activa
                                    @else
                                        @if ($promo->status == 'I')
                                            Inactiva
                                        @else
                                            N/A
                                        @endif
                                    @endif
                                    </th>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </hr>
        </div>
    </div>
</div>
<!-- Modal -->
<div class="modal modalAnimate" id="myModal" role="dialog">
    <div class="modal-dialog" id="modal01">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" id="modal_close_x" type="button">
                    ×
                </button>
                <h4 class="modal-title">Crear Lista de Descuento</h4>
            </div>
            <div class="modal-body">
                <form action="api/promo_list/store" id="promo_list_form" method="POST">
                    <input class="form-control" id="id" name="id" type="hidden">
                    <div class="form-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="panel panel-info">
                                    <div aria-expanded="true" class="panel-wrapper collapse in">
                                        <div class="panel-body">
                                            <h3 class="box-title">Informacion general</h3>
                                            <hr>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Nombre
                                                        </label>
                                                        <input class="form-control" id="name" name="name" placeholder="Nombre" type="text">

                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Duración (Días)
                                                        </label>
                                                        <input class="form-control" id="lifetime" name="lifetime" placeholder="Duración" type="number">
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label class="control-label">
                                                            Estado
                                                        </label>
                                                        <select class="form-control" id="status" name="status">
                                                            <option value=""> Seleccione... </option>
                                                            <option value="A"> Activo </option>
                                                            <option value="I"> Inactivo </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-actions modal-footer">
                            <button class="btn btn-success" onclick="save();" type="submit"><i class="fa fa-check"></i>Guardar</button>
                            <button class="btn btn-default" id="modal_close_btn" type="button">Close</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="js/promo_list/main.js?v=2.2"></script>
<script src="js/common-modals.js"></script>
@else
<center>
    <h3>
        Lo sentimos, Usted no posee permisos suficientes para acceder a este módulo
    </h3>    
</center>

@endif