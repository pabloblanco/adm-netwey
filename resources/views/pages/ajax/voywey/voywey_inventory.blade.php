{{--/*
Autor: Ing. LuisJ 
Contact: luis@gdalab.com
Marzo 2021
 */--}}
<div class="container-fluid">
  <div class="row bg-title">
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
      <h4 class="page-title">
        Reporte de inventario Voywey
      </h4>
    </div>
    <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12 d-flex justify-content-end">
      <ol class="breadcrumb">
        <li>
          <a href="#">
            Reportes
          </a>
        </li>
        <li class="active">
          Reporte de inventario Voywey
        </li>
      </ol>
    </div>
  </div>
</div>
<div class="container report-inventory">
  <div class="row justify-content-center">
    <div class="col-12" hidden="" id="rep-sc">
      <div class="row white-box">
        <div class="col-md-12">
          <h3 class="text-center">
            Reporte de inventario Voywey
          </h3>
        </div>
        <div class="col-md-12">
          <button class="btn btn-success m-b-20" id="download" type="button">
            Exportar reporte
          </button>
        </div>
        <div class="col-md-12">
          <div class="table-responsive">
            <form action="" class="form-horizontal" id="report_tb_form" method="POST" name="report_tb_form">
              {{ csrf_field() }}
            </form>
            <table class="table table-striped display nowrap" id="list-com">
              <thead>
                <tr>
                  <th>
                    Acciones
                  </th>
                  <th>
                    Bodega
                  </th>
                  <th>
                    Inventario fisico
                  </th>
                  <th>
                    Disponibles para vender
                  </th>
                  <th>
                    Asignados a repartidor
                  </th>
                  <th>
                    En ruta de entrega
                  </th>
                </tr>
              </thead>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="myModal" role="dialog">
  <div class="modal-dialog" id="modal01">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-dismiss="#myModal" data-modal="#myModal" id="modal_close_x" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
          Inventario asignado a los repartidores
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped display nowrap" id="myTableDetail">
                <thead>
                  <tr>
                    <th>
                      Acciones
                    </th>
                    <th>
                      Nombre del repartidor
                    </th>
                    <th>
                      Apellido del repartidor
                    </th>
                    <th>
                      Email del repartidor
                    </th>
                    <th>
                      SKU a entregar
                    </th>
                    <th>
                      Modelo del equipo a entregar
                    </th>
                    <th>
                      Cantidad de equipos asignados
                    </th>
                  </tr>
                </thead>
                <tbody id="inventory_detail">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="myModal2" role="dialog">
  <div class="modal-dialog" id="modal02">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-modal="#myModal2" id="modal_close_x2" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
          Detalle de inventario asignado
          <p class="mb-0">
            <strong>
              Repartidor:
            </strong>
            <span id="emailrepartidor">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              SKU:
            </strong>
            <span id="skurepartidor">
            </span>
          </p>
          <p class="mb-0">
            <strong>
              Bodega:
            </strong>
            <span id="bodegarepartidor">
            </span>
          </p>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped display nowrap" id="myTableDetailRepartidor">
                <thead>
                  <tr>
                    <th>
                      DN
                    </th>
                    <th>
                      Status
                    </th>
                  </tr>
                </thead>
                <tbody id="inventory_detailRepartidor">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="modal modalAnimate" id="myModal3" role="dialog">
  <div class="modal-dialog" id="modal03">
    <div class="modal-content">
      <div class="modal-header">
        <button class="modal_close_btn close" data-modal="#myModal3" id="modal_close_x3" type="button">
          ×
        </button>
        <h4 class="modal-title" id="modal-title">
          Inventario disponible en bodega
          <span id="name_bodega">
          </span>
        </h4>
      </div>
      <div class="modal-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table-responsive">
              <table class="table table-striped display nowrap" id="myTableDetailBodega">
                <thead>
                  <tr>
                    <th>
                      SKU
                    </th>
                    <th>
                      Modelo
                    </th>
                    <th>
                      MSISDN
                    </th>
                  </tr>
                </thead>
                <tbody id="inventory_detail_bodega">
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/voywey/voywey_inventory.js">
</script>
<script src="js/common-modals.js">
</script>