<div class="table-responsive">
  <table id="myTable" class="table table-striped">
    <thead>
      <tr>
        <th>Id</th>
        <th>Vendedor</th>
        <th>Coordinador</th>
        <th>N° deposito</th>
        <th>Monto</th>
        <th>Monto escrito</th>
        <th>Fecha de registro</th>
        <th>Fecha de deposito</th>
      </tr>
    </thead>
    <tbody>
      @foreach ($report as $sale)
        <tr>
          <td>{{ $sale->id}}</td>
          <td>{{ $sale->users_email }}</td>
          <td>{{ $sale->parent_email }}</td>
          <td>{{ $sale->n_tranfer }}</td>
          <td>{{ $sale->amount }}</td>
          <td>{{ $sale->amount_text }}</td>
          <td>{{ $sale->date_reg }}</td>
          <td>{{ $sale->date_dep }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>
</div>
<script src="js/reports/sellDepositDetail.js"></script>