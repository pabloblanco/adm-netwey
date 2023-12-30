<div class="container-fluid">
    <div class="row bg-title">
        <div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
            <h4 class="page-title">Asociación con Productos y Servicios</h4>
        </div>
        <div class="col-lg-9 col-sm-8 col-md-8 col-xs-12">
            <ol class="breadcrumb">
                <li><a href="/islim/">Dashboard</a></li>
                <li><a href="#" onclick="getview('pack');">Paquetes</a></li>
                <li class="active">Detalle</li>
            </ol>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <div class="card card-outline-primary text-center text-dark">
                    <div class="card-block">
                        <header>{{ $pack->title }}</header>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="control-label">Descripción:</label>
                                <p>
                                    {{ $pack->description }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Fecha de inicio:</label>
                                <p>
                                    {{ !empty($pack->date_ini)?$pack->date_ini:'N/A' }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <label class="control-label">Fecha de caducidad:</label>
                                <p>
                                    {{ !empty($pack->date_end)?$pack->date_end:'N/A' }}
                                </p>
                            </div>
                            <input type="hidden" id="pack_id" value="{{$pack->id}}">
                            <input type="hidden" id="pack_type" value="{{$pack->pack_type}}">
                            <input type="hidden" id="pack_service" value="{{!empty($pack->service) ? $pack->service : ''}}">
                            <input type="hidden" id="pack_product" value="{{!empty($pack->product) ? $pack->product : ''}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr>
    <div class="row">
        <div class="col-sm-12">
            <div class="white-box">
                <h3>Servicio asociado</h3>
                {{--@if (!$pack->cash or !$pack->credit)--}}
                <form id="pack_service_form" action="api/pack/service/{{ $pack->id }}" method="POST" @if (!empty($pack->service)) hidden @endif>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Forma de pago</label>
                                <select id="method_pay" name="method_pay" class="form-control">
                                    {{--@if (!$pack->cash and !$pack->credit)--}}
                                    <option value="">
                                        Seleccione una forma de pago...
                                    </option>
                                    <option value="CO">De contado</option>
                                    @foreach($financing as $f)
                                        <option value="{{$f->id}}" data-tam="{{$f->total_amount}}" data-f="{{$f->amount_financing}}" data-wf="{{$f->SEMANAL}}" data-wq="{{$f->QUINCENAL}}" data-mf="{{$f->MENSUAL}}">{{$f->name}}</option>
                                    @endforeach
                                    {{-- @endif --}}
                                </select>
                            </div>
                        </div>
                        <div class="col-md-8" id="service_id_container">
                            <div class="form-group">
                                <label class="control-label">Servicios</label>
                                <select id="service_id" name="service_id" class="form-control">
                                    <option value="">Seleccione un servicio...</option>
                                    @if(!empty($services))
                                    @foreach ($services as $service)
                                        <option price="{{ $service->price_pay }}" value="{{ $service->id }}">{{ $service->title }}</option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12 p-b-10" id="finan_detail_container" hidden>
                            <label class="col-md-12">
                                <b>Monto financiado: </b> $ <span id="mof"></span> 
                            </label>
                            <label class="col-md-12">
                                <b>Total a pagar: </b> $ <span id="ta"></span>
                            </label>
                            <label class="col-md-12">
                                <b>Cuota semanal: </b> $ <span id="wf"></span>
                            </label>
                            <label class="col-md-12">
                                <b>Cuota quincenal: </b> $ <span id="wq"></span>
                            </label>
                            <label class="col-md-12">
                                <b>Cuota mensual: </b> $ <span id="mf"></span>
                            </label>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Precio del módem</label>
                                <input type="number" id="price_pack" name="price_pack" class="form-control price">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Precio del servicio</label>
                                <input type="number" id="price_serv" name="price_serv" class="form-control price">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Precio total</label>
                                <input type="number" id="total_price" name="total_price" class="form-control" readonly="true">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{--@if (session('user')->platform == 'admin')
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label class="control-label">Estatus</label>
                                    <select name="status" class="form-control">
                                        <option value="A">Activo</option>
                                        <option value="I">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        @endif--}}
                        <div class="col-md-12" style="padding-top: 25px;">
                            <div class="form-actions modal-footer">
                                <button type="submit" class="btn btn-success" onclick="associateService({{ $pack->id }});">Asociar</button>
                            </div>
                        </div>
                    </div>
                </form>
                {{-- @endif --}}
                @if (!empty($pack->service))
                    <div class="table-responsive">
                        <table id="myTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Forma de pago</th>
                                    @if ($pack->type_buy == 'CR')
                                    <th>Financiamiento</th>
                                    @endif
                                    <th>Cuota inicial</th>
                                    <th>Precio del servicio</th>
                                    <th>Precio total</th>
                                    @if ($pack->type_buy == 'CO')
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{--@foreach ($pack->services as $service)--}}
                                <tr>
                                    <td>{{ $pack->service }}</td>
                                    <td>
                                        @if ($pack->type_buy == 'CO')
                                            De contado
                                        @else
                                            A crédito
                                        @endif
                                    </td>
                                    @if ($pack->type_buy == 'CR')
                                    <td>
                                        {{!empty($pack->financing)? $pack->financing : 'N/A'}}
                                    </td>
                                    @endif
                                    <td>{{ number_format($pack->price_pack,2,'.',',') }}</td>
                                    <td>{{ number_format($pack->price_serv,2,'.',',') }}</td>
                                    <td>{{ number_format($pack->total_price,2,'.',',') }}</td>
                                    @if ($pack->type_buy == 'CO')
                                    <td class="row">
                                        <button type="button" class="btn btn-danger btn-md button col-md-12" onclick="deassociateService('{{ $pack->id }}', '{{ $pack->service_id }}')">Desasociar</button>
                                    </td>
                                    @endif
                                </tr>
                                {{--@endforeach--}}
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-sm-12">
            <div class="white-box">
                <h3>Producto asociado</h3>
                <form id="pack_product_form" action="api/pack/product/{{ $pack->id }}" method="POST" @if(!empty($pack->product)) hidden @endif>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Productos</label>
                                <select id="inv_article_id" name="inv_article_id" class="form-control">
                                    <option value="">Seleccione un producto...</option>
                                    @if (empty($pack->product) && !empty($products))
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">
                                            {{ $product->title }}
                                        </option>
                                    @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                        {{-- @if (session('user')->platform == 'admin')
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="control-label">Estatus</label>
                                    <select name="status" class="form-control">
                                        <option value="A" selected>Activo</option>
                                        <option value="I">Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        @endif --}}
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="control-label">Tipo</label>
                                <select name="retail" id="retail" class="form-control">
                                    <option value="N" selected>Distribuidor</option>
                                    <option value="Y">Retail</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-actions modal-footer text-center">
                                @if (empty($pack->product))
                                    <button type="submit" class="btn btn-success" onclick="associateProduct({{ $pack->id }});">Asociar</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </form>
                @if (!empty($pack->product))
                    <div class="table-responsive">
                        <table id="myTable" class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Tipo</th>
                                    @if (empty($pack->service) || $pack->type_buy == 'CO')
                                    <th>Acciones</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                {{-- @foreach ($pack->products as $product) --}}
                                <tr>
                                    <td>{{ $pack->product }}</td>
                                    
                                    <td>{{$pack->retail == 'N'? 'Distribuidor' : 'Retail' }}</td>

                                    @if (empty($pack->service) || $pack->type_buy == 'CO')
                                    <td class="row">
                                        <button type="button" class="btn btn-danger btn-md button col-md-12" onclick="deassociateProduct('{{ $pack->id }}', '{{ $pack->product_id }}')">Desasociar</button>
                                    </td>
                                    @endif
                                </tr>
                                {{-- @endforeach --}}
                            </tbody>
                        </table>
                    </div>
                @else
                    <p>No hay productos asociados a este paquete</p>
                @endif
                <div class="row" id="association_container">
                </div>
            </div>
        </div>
    </div>
</div>
<script src="js/pack/detail.js"></script>