<section class="m-t-40">
    <div class="row white-box">
        <div class="col-md-12 p-t-20">
            <h3 class="text-center">Altas con Coordenadas Iguales</h3>
            <div class="table-responsive">
                <table id="tablebt" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Latitud</th>
                            <th>Longitud</th>
                            <th>Altas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($altas as $alta)
                        <tr>
                            <td>{{$alta->name}} {{$alta->last_name}}</td>
                            <td>{{empty($alta->phone)? 'N/A' : $alta->phone}}</td>
                            <td>{{$alta->users_email}}</td>
                            <td>{{$alta->lat}}</td>
                            <td>{{$alta->lng}}</td>
                            <td>{{$alta->coord}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<section class="m-t-40">
    <div class="row white-box">
        <div class="col-md-12 p-t-20">
            <h3 class="text-center">Altas con I.N.E Iguales</h3>
            <div class="table-responsive">
                <table id="tablebt2" class="table table-striped">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Cliente</th>
                            <th>I.N.E</th>
                            <th>Altas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($altasIne as $altaIne)
                        <tr>
                            <td>{{$altaIne->name}} {{$altaIne->last_name}}</td>
                            <td>{{empty($altaIne->phone)? 'N/A' : $altaIne->phone}}</td>
                            <td>{{$altaIne->users_email}}</td>
                            <td>{{$altaIne->cn}} {{$altaIne->cln}}</td>
                            <td>{{$altaIne->clients_dni}}</td>
                            <td>{{$altaIne->c_ine}}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>