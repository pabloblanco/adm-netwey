<style type="text/css">
    td.details-control {
        background: url('https://datatables.net/examples/resources/details_open.png') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('https://datatables.net/examples/resources/details_close.png') no-repeat center center;
    }
</style>
<div class="table-responsive">
  <table id="consumptionTable" class="table table-striped">
    <thead>
        <tr>
            <th></th>
            <th>Fecha consumo</th>
            <th>Consumo</th>
            <th>Throttling</th>
            <th></th>
            {{-- <th>Servicio</th> --}}
            {{-- <th>ID Oferta</th>
            <th>Oferta</th> --}}
            {{-- <th>Fecha activaci&oacute;n</th> --}}
            {{-- <th>Fecha corte</th> --}}
        </tr>
    </thead>
    <tbody>
        @foreach($report as $con)
        <tr>
            <td></td>
            <td>{{ $con->date_transaction }}</td>
            <td>{{ round(((($con->consuption/1024)/1024)/1024),2) == 0 ? round(((($con->consuption/1024)/1024)),2)." MB" : round(((($con->consuption/1024)/1024)/1024),2)." GB" }}</td>
            <td>{{ round(((($con->throttling/1024)/1024)/1024),2) == 0 ? round(((($con->throttling/1024)/1024)),2)." MB" : round(((($con->throttling/1024)/1024)/1024),2)." GB" }}</td>
            <td>{{ $con->msisdn }}</td>
            {{-- <th>{{ $con->title }}</th> --}}
            {{-- <th>{{ $con->codeAltan }}</th>
            <th>{{ $con->offer_name }}</th> --}}
            {{-- <th>{{ date('d-m-Y', strtotime($con->date_sup_be)) }}</th> --}}
            {{-- <th>{{ $con->date_sup_en + 1 }}</th> --}}
            {{-- <th>{{ date('d-m-Y', strtotime($con->date_sup_be.'+ '.($con->date_sup_en + 1 ).' days' )) }}</th> --}}
        </tr>
        @endforeach
    </tbody>
  </table>
</div>

<script type="text/javascript">



    $(document).ready(function () {
        var table=$('#consumptionTable').DataTable({
            searching: false,
            ordering: true,
            columns: [
                {
                    className:      'details-control',
                    orderable:      false,
                    data:           null,
                    defaultContent: ''
                },
                {
                    data: "fechaConsumo",
                    orderable:      false
                },
                {
                    data: "consumoGB",
                    orderable:      false
                } ,
                {
                    data: "throttlingGB",
                    orderable:      false
                } ,
                {
                    data: "msisdn",
                    className: 'd-none'
                }
            ],
            order: [[1, 'DESC']]
        });


        // Add event listener for opening and closing details
        $('#consumptionTable tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );

            if ( row.child.isShown() ) {
                // This row is already open - close it
                row.child.hide();
                tr.removeClass('shown');
            }
            else {
                // Open this row
                //row.child( format(row.data()) ).show();
                if($(this).data('fill')!='Y'){
                    params={
                        msisdn:row.data().msisdn,
                        date:row.data().fechaConsumo
                    };

                    elem=$(this);

                    $('.preloader').show();
                    request ('view/client/datatable-comp-details', 'POST', params,
                        function (res) {
                            if ( res.success ) {
                                elem.data('fill','Y');
                                row.child( res.msg );
                            } else {
                                row.child( 'Ocurrio un error en la consulta' );
                                console.log('error', res.errorMsg);
                            }
                            row.child.show();
                            tr.addClass('shown');
                            tr.next('tr').children('td').addClass('p-0');
                            $('.preloader').hide();
                        },
                        function (res) {
                            row.child( 'Ocurrio un error en la consulta' );
                            console.log('error', res.errorMsg);
                            row.child.show();
                            tr.addClass('shown');
                            $('.preloader').hide();
                        });


                    //row.child( row.data().msisdn );

                }
                else{
                    row.child.show();
                    tr.addClass('shown');
                }







            }
        } );

    });
</script>