@php
  $months = ["Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
  $month = $months[date('m')-2];
@endphp
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Netwey</title>

    <style type="text/css">
      /* CLIENT-SPECIFIC STYLES */
      body, table, td, a { -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; }
      table, td { mso-table-lspace: 0pt; mso-table-rspace: 0pt; }
      img { -ms-interpolation-mode: bicubic; }

      /* RESET STYLES */
      img { border: 0; outline: none; text-decoration: none; }
      table { border-collapse: collapse !important; }
      body { margin: 0 !important; padding: 0 !important; width: 100% !important; }

      /* iOS BLUE LINKS */
      a[x-apple-data-detectors] {
      color: inherit !important;
      text-decoration: none !important;
      font-size: inherit !important;
      font-family: inherit !important;
      font-weight: inherit !important;
      line-height: inherit !important;
      }

      /* ANDROID CENTER FIX */
      div[style*="margin: 16px 0;"] { margin: 0 !important; }

      /* MEDIA QUERIES */
      @media all and (max-width:639px){
      .wrapper{ width:320px!important; padding: 0 !important; }
      .container{ width:300px!important;  padding: 0 !important; }
      .mobile{ width:300px!important; display:block!important; padding: 0 !important; }
      .img{ width:100% !important; height:auto !important; }
      *[class="mobileOff"] { width: 0px !important; display: none !important; }
      *[class*="mobileOn"] { display: block !important; max-height:none !important; }
      }
    </style>
  </head>

  <body style="margin:0; padding:0; background-color:#fff; border: solid rgb(38, 46, 48) 5px; border-radius : 45px; overflow: hidden;">
    <div style="background-color: rgb(38, 46, 48);">
      <center>
        <img src="https://www.netwey.net/s/misc/logo.png">
      </center>
    </div>
    <br>
    <div style="padding-left: 15px;">
      <label><strong>En el siguiente enlace podras descargar la base de altas del mes {{$month}} del {{date('Y')}}. </strong></label>
      <br>
      <br>
      <br>
      <a href="{{$url}}">{{$url}}</a>
    </div>
    <br>
  </body>
</html>