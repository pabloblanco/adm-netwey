<?php
namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class CommonHelpers
{
  /*
  Guarda los reportes en el servidor y devuelve la ruta donde se almaceno (path), se puede pasar a url con
  storage_path('/app'.$path)
  Los archivos se guarda en storage/app/$basePath
  $basePath = ruta base de directorio donde se va a guardar el reporte "/public/reports"
  $directoryR = directorio donde se va a guardar el reporte, si no existe el metodo lo crea
  $data = data del reporte la primera fila son los titulos de la columnas
  $filename = Nombre del archivo sin extencion
  el archivo que almacena esta funcion es un xls
  NOTA: Si el excel tiene mas de 30000 lineas se dividira en paginas de 30000 cada una
   */
  public static function saveFile($basePath = false, $directoryR = false, $data = false, $filename = 'report', $limitFP = 30000, $ext = 'xls')
  {
    //Cargando directorios dentro de $basePath
    $directory  = Storage::disk('local')->directories($basePath);
    $pathReport = $basePath . '/' . $directoryR;

    //Si no existe el directorio de reporte para las altas se crea
    if (!in_array($pathReport, $directory)) {
      Storage::disk('local')->makeDirectory($pathReport);
    }

    //Guarda el excel en local
    \Excel::create($filename, function ($excel) use ($data, $limitFP, $ext) {
      if ($ext == 'xls') {
        if ((count($data) - 1) > $limitFP) {
          $pg = ceil((count($data) - 1) / $limitFP);

          $li = 0;

          for ($i = 1; $i <= $pg; $i++) {
            $part = array_slice($data, $li, $limitFP);

            if ($i > 1) {
              //$part = array_slice($data, $li, $limitFP);
              array_unshift($part, $data[0]);
              $li += $limitFP;
            } else {
              $li += $limitFP;
              $limitFP--;
            }

            $excel->sheet('Reporte ' . $i, function ($sheet) use ($part) {
              $sheet->fromArray($part, null, 'A1', false, false);
            });

          }
        } else {
          $excel->sheet('Reporte', function ($sheet) use ($data) {
            $sheet->fromArray($data, null, 'A1', false, false);
          });
        }
      } else {
        $excel->sheet('Reporte', function ($sheet) use ($data) {
          $sheet->fromArray($data, null, 'A1', false, false);
        });
      }
    })->store($ext, storage_path('app' . $pathReport)); //xls

    //Armando rutas
    $filename      = $filename . '.' . $ext; //xls
    $fullLocalPath = $pathReport . '/' . $filename;
    $s3FullPath    = 'reports/' . $directoryR . '/' . $filename;

    //Subiendo el excel a s3
    Storage::disk('s3')->put($s3FullPath, file_get_contents(storage_path('app' . $fullLocalPath)), 'public');

    //Borrando el excel del local
    Storage::disk('local')->delete($fullLocalPath);

    //Retorna url de s3
    return Storage::disk('s3')->url($s3FullPath);
  }

  /*Verifica recaptcha de google*/
  public static function veifyCaptchaGoogle($data = false)
  {
    if ($data) {
      $curl = curl_init();

      curl_setopt_array($curl, array(
        CURLOPT_URL            => env('URL_VERIFY_CAPTCHA'),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 60,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => "POST",
        CURLOPT_POSTFIELDS     => $data,
        CURLOPT_HTTPHEADER     => array(
          "accept: */*",
          "Content-Type: application/x-www-form-urlencoded",
          "cache-control: no-cache",
        ),
      ));

      $response = curl_exec($curl);
      $err      = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return ['success' => false, 'data' => $err];
      } else {
        return ['success' => true, 'data' => json_decode($response)];
      }

    }

    return ['success' => false, 'data' => 'Faltan datos.'];
  }

  public static function getMemoryUse()
  {
    $memory = memory_get_usage(true);

    if ($memory > 1024 * 1024) {
      $memory = round($memory / 1024 / 1024, 2) . ' MB';
    } elseif ($memory > 1024) {
      $memory = round($memory / 1024, 2) . ' KB';
    }

    return $memory;
  }
  /**
   *  Ejecuta un curl
   *
   *  @param String url endpoint
   *  @param String type tipo de ejecucion [GET, POST, DELETE, ..]
   *  @param Array header campo opcional, de ser enviado reemplaza la cabecera que se envia en el curl
   *  @return Array
   */
  public static function executeCurl($url = false, $type = false, $header = [], $data = [])
  {
    if ($url && $type) {
      $curl = curl_init();

      if (!count($header)) {
        $header = [
          "accept: */*",
          "Content-Type: application/json",
          "cache-control: no-cache",
          "accept-language: en-US,en;q=0.8",
        ];
      }

      $options = [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING       => "",
        CURLOPT_MAXREDIRS      => 10,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST  => $type,
        CURLOPT_HTTPHEADER     => $header,
      ];

      if (is_array($data) && count($data) && strtoupper($type) == 'POST') {
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
      }

      curl_setopt_array($curl, $options);

      $response = curl_exec($curl);
      $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
      $err      = curl_error($curl);

      curl_close($curl);

      if ($err) {
        return [
          'success' => false,
          'data'    => $err,
          'code'    => !empty($httpcode) ? $httpcode : 0,
        ];
      } else {
        $dataJson = json_decode($response);

        if (!empty($dataJson)) {
          return [
            'success'  => true,
            'data'     => $dataJson,
            'original' => $response,
            'code'     => !empty($httpcode) ? $httpcode : 0,
          ];
        } else {
          return [
            'success'  => false,
            'data'     => 'No se pudo obtener json.',
            'original' => $response,
            'code'     => !empty($httpcode) ? $httpcode : 0,
          ];
        }

      }
    }

    return ['success' => false, 'data' => 'Faltan datos.'];
  }

  public static function getFloatValue($string)
  {
    $string = str_replace('$', '', $string);
    $string = str_replace(' ', '', $string);
    $string = str_replace(',', '', $string);
    //$string = str_replace(',', '.', $string);

    if (!empty($string)) {
      return floatval($string);
    }

    return 0;
  }

  public static function getCSVData($filepath, $bank, $delimiter = ',')
  {
    $file = fopen($filepath, "r");

    if ($file !== false) {
      ini_set('auto_detect_line_endings', true);

      $c   = 0;
      $res = [
        'OK'     => [],
        'NOT_OK' => [],
      ];

      while (($datos = fgetcsv($file, 0, $delimiter)) !== false) {
        if (count($datos) == $bank->column) {
          if ($bank->group == 'BV') {
            if (!empty($datos[5])) {
              $amount = self::getFloatValue($datos[5]);

              if ($amount > 0) {
                if (preg_match("/[a-z]{2}\d{4}/i", $datos[3], $matches)) {
                  $res['OK'][] = [
                    'hash'       => base64_encode($datos[3] . $datos[5] . $datos[6]),
                    'cod'        => strtoupper($matches[0]),
                    'concepto'   => $datos[2] . '-' . $datos[3],
                    'amount'     => $amount,
                    'amount_txt' => '$' . number_format($amount, 2, '.', ','),
                    'date_dep'   => Carbon::createFromFormat('d/m/Y', $datos[0])->format('Y-m-d'),
                    'line'       => implode(' + ', $datos),
                    'date_load'  => date('Y-m-d H:i:s'),
                    'bank'       => 'BBVA'];
                } else {
                  $res['NOT_OK'][] = [
                    'hash'       => base64_encode($datos[3] . $datos[5] . $datos[6]),
                    'concepto'   => $datos[2] . '-' . $datos[3],
                    'amount'     => $amount,
                    'amount_txt' => '$' . number_format($amount, 2, '.', ','),
                    'date_dep'   => Carbon::createFromFormat('d/m/Y', $datos[0])->format('Y-m-d'),
                    'reason'     => 'No se detecto el código',
                    'line'       => implode(' + ', $datos),
                    'date_load'  => date('Y-m-d H:i:s'),
                    'bank'       => 'BBVA'];
                }

                $c++;
              }
            }
          }

          if ($bank->group == 'AZ') {
            if (!empty($datos[4])) {
              $amount = self::getFloatValue($datos[4]);

              if ($amount > 0) {
                if (preg_match("/[.]\d{1,2}/i", $datos[4], $matches)) {
                  $cod = str_replace('.', '', $matches[0]);
                  $cod = strlen($cod) == 1 ? $cod . '0' : $cod;

                  $res['OK'][] = [
                    'hash'       => base64_encode($datos[1] . $datos[3] . $datos[4] . $datos[5] . $datos[6]),
                    'cod'        => $cod,
                    'concepto'   => $datos[3],
                    'amount'     => $amount,
                    'amount_txt' => '$' . number_format($amount, 2, '.', ','),
                    'date_dep'   => Carbon::createFromFormat('Y-m-d', $datos[1])->format('Y-m-d'),
                    'line'       => implode(' + ', $datos),
                    'date_load'  => date('Y-m-d H:i:s'),
                    'bank'       => 'Azteca'];
                } else {
                  $res['NOT_OK'][] = [
                    'hash'       => base64_encode($datos[1] . $datos[3] . $datos[4] . $datos[5] . $datos[6]),
                    'concepto'   => $datos[3],
                    'amount'     => $amount,
                    'amount_txt' => '$' . number_format($amount, 2, '.', ','),
                    'date_dep'   => Carbon::createFromFormat('Y-m-d', $datos[1])->format('Y-m-d'),
                    'reason'     => 'No se detecto el código',
                    'line'       => implode(' + ', $datos),
                    'date_load'  => date('Y-m-d H:i:s'),
                    'bank'       => 'Azteca'];
                }

                $c++;
              }
            }
          }
        }
      }

      ini_set('auto_detect_line_endings', false);
      fclose($file);

      if ($c > 0) {
        return [
          'success'       => true,
          'data'          => $res,
          'total_process' => $c];
      }

      return ['success' => false, 'msg' => 'No se procesaron depósitos'];
    } else {
      return ['success' => false, 'msg' => 'Error en archivo'];
    }
  }

  /* validacion de fechas de filtro de busqueda */
  public static function validateDate($filters)
  {
    //Validando que vengan los dos rangos de fechas y formateando fecha
    if (empty($filters['dateStar']) && empty($filters['dateEnd'])) {
      $filters['dateStar'] = Carbon::now()
        ->format('Y-m-d H:i:s');

      $filters['dateEnd'] = Carbon::now()
        ->addMonth()
        ->format('Y-m-d H:i:s');
    } elseif (empty($filters['dateStar']) && !empty($filters['dateEnd'])) {
      $filters['dateEnd'] = Carbon::createFromFormat('d-m-Y', $filters['dateEnd'])
        ->endOfDay()
        ->toDateTimeString();

      $filters['dateStar'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateEnd'])
        ->subMonth()
        ->startOfDay()
        ->toDateTimeString();
    } elseif (empty($filters['dateEnd']) && !empty($filters['dateStar'])) {
      $filters['dateStar'] = Carbon::createFromFormat('d-m-Y', $filters['dateStar'])
        ->startOfDay()
        ->toDateTimeString();

      $filters['dateEnd'] = Carbon::createFromFormat('Y-m-d H:i:s', $filters['dateStar'])
        ->endOfDay()
        ->addMonth()
        ->toDateTimeString();
    } else {
      $filters['dateStar'] = Carbon::createFromFormat('d-m-Y', $filters['dateStar'])
        ->startOfDay()
        ->toDateTimeString();

      $filters['dateEnd'] = Carbon::createFromFormat('d-m-Y', $filters['dateEnd'])
        ->endOfDay()
        ->toDateTimeString();
    }

    $filters['dateStar'] = (date("Ymd000000", strtotime($filters['dateStar'])));
    $filters['dateEnd']  = (date("Ymd235959", strtotime($filters['dateEnd'])));

    return $filters;
  }

/**
 * [getOptionColumn Devuelve los enum de una tabla]
 * @param  [type] $table [nombre de la tabla a la cual extraer informacion]
 * @param  [type] $field [nombre de la columna a revisar]
 * @return [type]        [description]
 */
  public static function getOptionColumn($table, $field)
  {
    //$table = 'islim_portability';
    //$field = 'status';

    $test = DB::select(DB::raw("show columns from {$table} where field = '{$field}'"));

    preg_match('/^enum\((.*)\)$/', $test[0]->Type, $matches);
    foreach (explode(',', $matches[1]) as $value) {
      $enum[] = trim($value, "'");
    }
    asort($enum);
    return $enum;
  }
}
