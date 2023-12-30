<?php
/*
Septiembre 2022
 */
namespace App\Helpers;

use Illuminate\Http\Request;

use App\Helpers\Curl;

//
class Slack {

  /**
   * 
   * Contiene una lista de todos los request disponibles de Slack con la estructura y descripción con la cual se debe llamar a cada uno de ellos.
   * Cada servicio contiene la siguiente estructura:
   * 
   * @var url: Contiene la url a ejecutar sobre Google ya sea completa (Incluyendo el http:...) o únicamente el terminal de la misma
   * @var method: Indica el método HTTP con el cual se debe ejecutar el servicio.
   * @var paramsFormat: Indica el formato en el cual se debe enviar la información (Aplica solo a los objetos enviados en el servicio o al cuerpo de la llamada).
   * @var usesParamId: Indica si usa algún identificador como sufijo del servicio (eje. http://test.for.api.telmovpay.com/SERVICIO_CONSULTADO/SUFIJO)
   * @var params: Representa la estructura de los elementos a enviar en el servicio. Se debe leer atentamente cada elemento dado que algunos son un sufijo del URL, otros son objetos que se envían como parámetro GET en el servicio y otros viajan directamente en el cuerpo de la llamada.
   * @var notes: Contiene alguna información relevante para la implementación del servicio
   * 
   */
  public static $REQUESTS = [
    'slack-notification' => [
      'description' => 'Envía notificaciones a la plataforma de Slack para mantener reportes de errores.',
      'url' => 'URL_SLACK',
      'method' => 'POST',
      'paramsFormat' => 'json',
      'usesParamId' => false,
      'params' => [
        'text' => 'Parámetro enviado en el cuerpo de la llamada. Representa el mensaje de cabecera de la notificación.',
        'attachments' => 'Parámetro enviado en el cuerpo de la llamada. Representa un arreglo de información relacionado con los errores.',
          'footer' => 'Parámetro enviado en la información relacionada a los errores. Representa un texto incluido en el final del reporte.',
          'ts' => 'Parámetro enviado en la información relacionada a los errores. Contiene la hora del reporte.',
          'color' => 'Parámetro enviado en la información relacionada a los errores. Indica el color a mostrarse en el reporte de Slack para esta notificación.',
          'pretext' => 'Parámetro enviado en la información relacionada a los errores. Mensaje de notificación a enviar.',
          'fields' => 'Parámetro enviado en la información relacionada a los errores. Arreglo de elementos descriptivos asociados a la información relacionada con los errores.',
            'title' => 'Parámetro enviado en el arreglo de elementos descriptivos asociados a la información relacionada con los errores. Encabezado lógico del error generado.',
            'value' => 'Parámetro enviado en el arreglo de elementos descriptivos asociados a la información relacionada con los errores. Información que causó el error.'
      ]
    ]
  ];

  public function __construct() {

    date_default_timezone_set('America/Mexico_City');

  }

  /**
   * [sendSlackNotification Envio de notificaciones al Slack]
   * @param  string  $message [description]
   * @param  string  $type    [Indicar si es Error, Alert, Warning]
   * @param  array   $data    [description]
   * @param  boolean $request [description]
   * @return [type]           [description]
   */
  public static function sendSlackNotification($message = '', $type = 'ALERT', $data = [], $request = false, $data_return = false, $time = false, Request $systemRequest = null) {

    $send = [
      'text'        => 'Mensaje de notificación',
      'attachments' => [[
        'footer'  => 'Fecha de la notificación',
        'ts'      => time(),
        'color'   => $type == 'ALERT' ? 'danger' : 'good',
        'pretext' => $message,
        'fields'  => [
          [
            'title' => 'Host',
            'value' => !empty($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST'],
            'short' => false,
          ],
        ],
      ]],
    ];

    if ($request) {

      if (!empty($request->ip()))
        $send['attachments'][0]['fields'][] = [
          'title' => 'IP origin',
          'value' => $request->ip(),
          'short' => false,
        ];

      if (!empty($request->method()))
        $send['attachments'][0]['fields'][] = [
          'title' => 'Method',
          'value' => $request->method(),
          'short' => false,
        ];

      if (!empty($request->path()))
        $send['attachments'][0]['fields'][] = [
          'title' => 'URL',
          'value' => $request->path() . '/',
          'short' => false,
        ];

      if (!empty($request->header()))
        $send['attachments'][0]['fields'][] = [
          'title' => 'Headers',
          'value' => (string) json_encode($request->header()),
          'short' => false,
        ];

      if (!empty($data_return))
        $send['attachments'][0]['fields'][] = [
          'title' => 'Data received',
          'value' => (string) json_encode($data_return),
          'short' => false,
        ];

    }

    if (!empty($data))
      $send['attachments'][0]['fields'][] = [
        'title' => 'Data Send -------',
        'value' => '',
        'short' => false,
      ];

    if (!empty($data)) {

      //Valores enviados en la consulta
      if (count($data)) {

        foreach ($data as $key => $value)
          if ($key != 'usuario' && $key != 'password')
            $send['attachments'][0]['fields'][] = [
              'title' => $key,
              'value' => $value,
              'short' => false,
            ];
          else
            $send['attachments'][0]['fields'][] = [
              'title' => $key,
              'value' => "Lo sentimos, es un dato sencible que no se puede mostrar",
            ];

      }

    }

    if (!empty($data))
      $send['attachments'][0]['fields'][] = [
        'title' => '---------------',
        'value' => '',
        'short' => false,
      ];

    if (!empty($time))
      $send['attachments'][0]['fields'][] = [
        'title' => 'Response time',
        'value' => $time . ' Segundos',
        'short' => false,
      ];

/*
    $res = Curl::execute(
      'slack-notification',
      null,
      $send,
      $systemRequest
    );
*/
    $res['success'] = false;

    if ($res['success'])
      return 'OK';
    else
      return 'NOT_OK';

  }

}
