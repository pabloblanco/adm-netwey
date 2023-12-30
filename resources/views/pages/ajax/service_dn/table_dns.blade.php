<div class="col-md-12">
    <div class="form-group">
        <label class="control-label">Nombre</label>
        <input type="text" id="nameList" name="nameList" class="form-control" value="{{$dataList->name}}" placeholder="Nombre de la lista" required="">
        <input type="hidden" name="list" id="list" value="{{$dataList->id}}">
    </div>
</div>
<div class="col-md-12">
    <div class="form-group">
        <label class="control-label">Agregar MSISDN</label>
        <div class="form-group">
            <label class="form-check-label bt-switch">
                <input type="checkbox" id="dnm" class="form-check-input"> Ingresar MSISDN manualmente
            </label>
            <div class="p-t-10" id="file-content">
                <label class="control-label">Archivo CSV con MSISDN</label>
                <input type="file" id="msisdn_file" name="msisdn_file" class="form-control-file">
            </div>
        </div>

        <div id="dn-content" class="hidden">
            <select id="dns" name="dns[]" class="form-control" placeholder="Seleccione un dn" multiple>
                <option value="">Seleccione un MSISDN</option>
            </select>
        </div>
    </div>
</div>
<div class="col-md-12 text-center">
    <button type="button" class="btn btn-info btn-md" id="saveEditList">Guardar</button>
</div>
<hr/>
<div class="col-md-12 p-t-20">
    <div class="table-responsive">
        <table id="listdns" class="table table-striped">
            <thead>
                <tr>
                    <th>Accion</th>
                    <th>Nombre</th>
                    <th>MSISDN</th>
                </tr>
            </thead>
            <tbody>
                @foreach($datadn as $dn)
                    <tr>
                        <td>
                            <button type="button" class="btn btn-danger btn-md delete-ldn" data-dn="{{$dn->msisdn}}">
                                Eliminar
                            </button>
                        </td>
                        <td>
                            {{$dn->name}} {{$dn->last_name}}
                        </td>
                        <td>
                            {{$dn->msisdn}}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>