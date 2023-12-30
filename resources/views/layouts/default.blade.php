<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('includes.head')
  </head>
  @if(env('APP_ENV') == 'local')
    <style type="text/css">
      .navbar-header {
          background-color: #2c2e34 !important;
      }
    </style>
  @endif

  <body>
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>

    <div id="wrapper">

      @include('includes.header')
      @include('includes.sidebar')

        <div id="page-wrapper">
          @yield('content')
        </div>

      @include('includes.footer')

    </div>

    @include('includes.script')
    @yield('script')

    <script type="text/javascript">
      $(function(){
        function bindClick(e){
          $('.preloader').fadeIn();

          e.preventDefault();

          let report = $(this).data('report');

          $('.new-report').attr('hidden', true);

          $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "{{ route('checkReport') }}",
            method: 'POST',
            data: {report: report},
            dataType: 'json'
          });

          const url = $(this).attr('href');

          if(url.indexOf('.csv') !== -1){
            fetch(url)
            .then(res => res.text())
            .then(data => {
              download(data, 'reporte.csv', 'text/csv');
              $('.preloader').hide();
            })
            .catch(function(error) {
              alert('No se pudo descargar el archivo');
              $('.preloader').hide();
            });
          }else{
            let link = document.createElement('a');
            link.href = url;
            link.download = 'reporte.xls';

            $('.preloader').hide();
            if(document.createEvent) {
              let e = document.createEvent('MouseEvents');
              e.initEvent('click', true, true);
              link.dispatchEvent(e);
            }else{
              window.open(url, 'reporte.xls');
            }
          }
        }

        function checkReports(){
          $.ajax({
            headers: {
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
            url: "{{ route('reportNoti') }}",
            method: 'POST',
            dataType: 'json',
            success: function (res) {
                if(res.success){
                    if(res.data.length){
                        var html1 = '';
                        var html2 = '';

                        $('.noti-report .drop-title').text('Último(s) '+res.data.length+' reporte(s) generado(s)');

                        res.data.forEach(function(ele){
                          if(ele.status == 'G' || ele.status == 'P' || ele.status == 'E'){
                            if(ele.status == 'G'){
                              html1 += '<a href="'+ele.download_url+'" class="download-report" data-report="'+ele.id+'">';
                            }else{
                              html1 += '<a href="#">';
                            }

                            html1 += '<h5>'
                            html1 += '<i class="ti-download"></i>'+ele.name_report.replace(/[_]/g,' ');
                            html1 += '<br>';
                            if(ele.status == 'G'){
                              html1 += '(Generado)';
                            }
                            if(ele.status == 'P'){
                              html1 += '(Generándose)';
                            }
                            if(ele.status == 'E'){
                              html1 += '(Error)';
                            }
                            html1 += '</h5>';
                            html1 += '<p>'+ele.date_reg+'</p>';
                            html1 += '</a>';
                          }else{
                            html2 += '<a href="'+ele.download_url+'" class="download-report" data-report="'+ele.id+'">';
                            html2 += '<h5><i class="ti-check-box"></i>'+ele.name_report.replace(/[_]/g,' ')+'</h5>';
                            html2 += '<p>'+ele.date_reg+'</p>';
                            html2 += '</a>';
                          }
                        });

                        if(html1 != '')
                          $('.new-report').attr('hidden', null);
                        else
                          $('.new-report').attr('hidden', true);

                        $('#notification-content').html(html1);
                        $('#notification-content').append(html2);

                        $('.download-report').bind('click', bindClick);

                        $('.noti-report').attr('hidden', null);
                    }else{
                      $('#notification-content').html('');
                      $('.noti-report').attr('hidden', true);
                    }
                }else{
                    alert(res.msg);
                }
            },
            error: function (res) {
                alert('Fallo la consulta de notificacicones.');
            }
          });
        }

        checkReports();

        setInterval(checkReports, (1000 * 60 * {{ env('FREQUENCY_REPORT', 3) }}));
    });
    </script>
  </body>
</html>
