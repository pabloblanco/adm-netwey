<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MainMenuItem extends Model
{

  protected $table = 'islim_main_menu';

  protected $fillable = [
    'id', 'parent_id', 'description', 'platform', 'order', 'url', 'status',
  ];

  public $incrementing = false;

  public $timestamps = false;

  /**
  @params
  - id, el identificador del menú/submenú
  - $childsView, Lista de identificadores de submenú
  - $policies, Lista de códigos de políticas de accesos a submenú (Collection)
  -
   */
  public static function getItem($id, $policies = false)
  {
    $item = MainMenuItem::where(['id' => $id, 'status' => 'A', 'platform' => 'admin'])->first();

    if (isset($item)) {
      $child = MainMenuItem::where(['parent_id' => $id, 'status' => 'A', 'platform' => 'admin'])->orderBy('order', 'asc')->get();

      //file_put_contents("salida.txt",  "child=".print_r([$child], true) . "\n", FILE_APPEND);

      if (!empty($child)) {
        $childs = array();
        switch ($id) {

          case 1: //Usuarios

            foreach ($child as $key) {
              if ($key->id == 123) {
                //1=Gestion de usuarios
                if (User::hasPermission(session('user')->email, 'AMU-RUS')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 124) {
                // 124=Solicitus de baja
                if (User::hasPermission(session('user')->email, 'LOW-REQ')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 127) {
                // 127=Reporte de baja en proceso
                if (User::hasPermission(session('user')->email, 'LOW-SPB')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 128) {
                // 128=Carga de archivo de finiquito de baja
                if (User::hasPermission(session('user')->email, 'LOW-FFB')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 131) {
                //solicitud de bajas de usuarios
                if (User::hasPermission(session('user')->email, 'LOW-SBU')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 132) {
                //listado de solicitudes de bajas de usuarios
                if (User::hasPermission(session('user')->email, 'LOW-LRL')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 135) {
                //Esquema comercial de usuarios
                if (User::hasPermission(session('user')->email, 'USR-SCH')) {
                  $childs[] = $key;
                }
              }elseif ($key->id == 145) {
                //Politicas predeterminadas
                if (User::hasPermission(session('user')->email, 'USR-LPP')) {
                  $childs[] = $key;
                }
              }elseif ($key->id == 146) {
                //Asignacion de Politicas de forma masiva 
                if (User::hasPermission(session('user')->email, 'USR-LAP')) {
                  $childs[] = $key;
                }
              }elseif ($key->id == 150) {
                //Distribuidores de Usuarios
                if (User::hasPermission(session('user')->email, 'DDU-RDU')) {
                  $childs[] = $key;
                }
              }
            }
            break;
          case 2: //concentradores
            foreach ($child as $key) {
              if ($key->id == 26) {
                //asignacion de saldo
                if (User::hasPermission(session('user')->email, 'AMC-ABC')) {
                  $childs[] = $key;
                }

              } else {
                $childs[] = $key;
              }
            }
            break;
          case 3: //inventarios
            foreach ($child as $key) {
              if ($key->id == 5) {
                //5=proveedores
                if (User::hasPermissionPack(session('user')->email, ['A1P'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 6) {
                // 6=Bodegas
                if (User::hasPermissionPack(session('user')->email, ['A1W'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 7) {
                //7=Productos
                if (User::hasPermissionPack(session('user')->email, ['A2P'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 8) {
                //8=Detalle de productos
                if (User::hasPermissionPack(session('user')->email, ['ADP'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 139) {
                //139=Lista de Descuentos
                if (User::hasPermissionPack(session('user')->email, ['LDD'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 9) {
                //9=Servcios
                if (User::hasPermissionPack(session('user')->email, ['A1S'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 83) {
                //83 servicios blim
                if (User::hasPermissionPack(session('user')->email, ['A1B'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 10) {
                //10=Paquetes
                if (User::hasPermissionPack(session('user')->email, ['A3P'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 11) {
                //11=AsigCoordinadores
                if (User::hasPermissionPack(session('user')->email, ['A1V'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 14) {
                //14=mov. entre bodegas
                if (User::hasPermissionPack(session('user')->email, ['A2W'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 86) {
                //86 servicios promocionales
                if (User::hasPermissionPack(session('user')->email, ['SRP'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 59) {
                //59 Financiamientos
                if (User::hasPermissionPack(session('user')->email, ['FIN'])) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 111) {
                //111 Estatus de inventarios
                if (User::hasPermission(session('user')->email, 'EIV-REI')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 113) {
                //113 Validar Estatus de inventarios
                if (User::hasPermission(session('user')->email, 'EIV-VEI')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 137) {
                //137 Historico de Estatus de Inventario
                if (User::hasPermission(session('user')->email, 'EIV-HSI')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 115) {
                //115 Guias de inventarios pendientes por procesar
                if (User::hasPermission(session('user')->email, 'EIV-GIP')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 122) {
                //118 Reporte merma equipos viejos
                if (User::hasPermission(session('user')->email, 'EIV-MEV')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 130) {
                //130 Reporte reciclaje de DN
                if (User::hasPermission(session('user')->email, 'INV-RCL')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 129) {
                // 129=Ver listado de descuentos segun kpi
                if (User::hasPermission(session('user')->email, 'LOW-KPI')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 133) {
                // actualizacion masivas de ids
                if (User::hasPermission(session('user')->email, 'INV-UMI')) {
                  $childs[] = $key;
                }
              }
            }

            break;
          case 4: //vendedores
            foreach ($child as $key) {
              if ($key->id == 13) {
                //13=conciliacion de depositos
                if (User::hasPermission(session('user')->email, 'SEL-DCC')) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 27) {
                //27=asignacion de saldos
                if (User::hasPermission(session('user')->email, 'SEL-ASB')) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 63) {
                //63=Id de depositos
                if (User::hasPermission(session('user')->email, 'SEL-IDD')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 91) {
                //91=conciliacion de depositos Usuarios Eliminados
                if (User::hasPermission(session('user')->email, 'SEL-DCI')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 96) {
                //96=Id de depositos Vend Eliminados
                if (User::hasPermission(session('user')->email, 'SEL-IDE')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 106) {
                //106=Deuda de coordinadores
                if (User::hasPermission(session('user')->email, 'SEL-LDC')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 112) {
                //Pedido sugerido
                if (User::hasPermission(session('user')->email, 'SEL-LPS')) {
                  $childs[] = $key;
                }
              }
            }
            break;

          case 28: //clientes
            foreach ($child as $key) {
              if ($key->id == 47) {
                //cliente
                if (User::hasPermissionPack(session('user')->email, ['CLA'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 46) {
                //Sim swap
                if (User::hasPermissionPack(session('user')->email, ['INV-SSW'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 56) {
                //Articulos vendidos no activos
                if (User::hasPermissionPack(session('user')->email, ['ARV-DSE'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 62) {
                //Servicialidad
                if (User::hasPermissionPack(session('user')->email, ['INV-SMP'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 81) {
                //recompra
                if (User::hasPermissionPack(session('user')->email, ['CLI-SAB'])) {
                  $childs[] = $key;
                }

              } elseif ($key->id == 90) {
                //portabilidad
                if (User::hasPermissionPack(session('user')->email, ['CLI-PRT'])) {
                  $childs[] = $key;
                }

              }
            }
            break;

          case 15: //reportes
            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ESL';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 16;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ALT';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 17;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SEL';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 19;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-REC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 18;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-CON';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 20;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-IWH';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 21;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ISC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 22;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-CLI';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 23;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-PRO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 24;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ASR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 29;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-EOO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 31;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-UDN';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 32;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-CSM';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 33;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SWP';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 48;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-CIB';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 49;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-VAN';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 57;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-AAV';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 58;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //OJO no esta en la bd
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-FIN';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 61;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SCO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 66;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RMU';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 64;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RRE';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 67;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SIR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 72;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RRA';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 73;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SIC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 75;
                });
              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RCP';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 77;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RCC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 80;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RPG';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 84;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RSR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 85;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-PJY';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 87;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-COO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 88;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-UDC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 89;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RUB';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 98;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-MIG';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 99;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }
              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SSM';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 100;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ACC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 101;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-SHY';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 105;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-CPS';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 107;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-JLU';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 110;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-ROR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 114;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RIT';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 116;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-IWM';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 117;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-FIR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 118;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-PGT';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 134;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-RIP';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 149;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }

              if ($policies->search(function ($item, $key) {return $item->code == 'REP-TLP';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 147;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }

            }
            break;

          case 97: //reportes voywey
            if ($policies && $policies->count() > 0) {
              //Reporte de ventas de jelou
              if ($policies->search(function ($item, $key) {return $item->code == 'VOY-SJE';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 92;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //Reporte de inventario de voywey
              if ($policies->search(function ($item, $key) {return $item->code == 'VOY-IVN';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 93;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //Reporte de dventas hechas aun por conciliacion voywey
              if ($policies->search(function ($item, $key) {return $item->code == 'VOY-CON';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 94;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //Reporte de ordenes de voywey
              if ($policies->search(function ($item, $key) {return $item->code == 'VOY-ROV';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 95;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 103: //Facturacion
            if ($policies && $policies->count() > 0) {
              //Agregar Conceptos
              if ($policies->search(function ($item, $key) {return $item->code == 'BIL-CBC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 104;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              //facturacion masiva oxxo
              if ($policies->search(function ($item, $key) {return $item->code == 'BIL-MSO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 136;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              //reporte de facturacion masiva
              if ($policies->search(function ($item, $key) {return $item->code == 'BIL-RFM';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 138;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 108: //Coppel
            if ($policies && $policies->count() > 0) {
              //Flujo Altas Fallidas
              if ($policies->search(function ($item, $key) {return $item->code == 'COP-UFS';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 109;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 34: //brighstart
            foreach ($child as $key) {
              $childs[] = $key;
            }
            break;

          case 36:
            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-BTO';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 37;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-BRE';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 38;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-BAT';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 39;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-CHR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 40;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-DEC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 41;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-ARA';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 42;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-ARB';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 43;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-MIR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 44;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-QTA';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 45;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }

            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'RBI-CH3';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 79;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 50:
            if ($policies && $policies->count() > 0) {
              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-SLS';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 51;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-USR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 52;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-CVA';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 74;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-INC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 76;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-LEC';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 78;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-RPP';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 79;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-CVS';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 82;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }

              if ($policies->search(function ($item, $key) {return $item->code == 'ROS-SAL';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 102;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 54:
            if ($policies && $policies->count() > 0) {
              //Creacion de concentradores
              if ($policies->search(function ($item, $key) {return $item->code == 'CHA-CRE';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 55;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //Servicios por dn
              if ($policies->search(function ($item, $key) {return $item->code == 'CHA-LIS';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 53;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }
              }
            }
            break;

          case 68:
            if ($policies && $policies->count() > 0) {
              //Configuración
              if ($policies->search(function ($item, $key) {return $item->code == 'ABO-POR';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 69;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
              //Asignación de módems
              if ($policies->search(function ($item, $key) {return $item->code == 'ABO-ASM';}) !== false) {
                $itm = $child->first(function ($value, $key) {
                  return $value->id == 70;
                });
                if (!empty($itm)) {
                  $childs[] = $itm;
                }

              }
            }
            break;

          case 119: //reportes portabilidad

            foreach ($child as $key) {
              if ($key->id == 120) {
                //120 Reporte de importaciones a netwey
                if (User::hasPermission(session('user')->email, 'P0R-IMP')) {
                  $childs[] = $key;
                }
              } elseif ($key->id == 121) {
                //121 Reporte de exportaciones de netwey
                if (User::hasPermission(session('user')->email, 'P0R-EXP')) {
                  $childs[] = $key;
                }
              }
            }
/*
if ($policies && $policies->count() > 0) {
//Reporte de importacion
if ($policies->search(function ($item, $key) {return $item->code == 'P0R-IMP';}) !== false) {
$itm = $child->first(function ($value, $key) {
return $value->id == 120;
});
if (!empty($itm)) {
$childs[] = $itm;
}
}
//Reporte de exportacion
if ($policies->search(function ($item, $key) {return $item->code == 'P0R-EXP';}) !== false) {
$itm = $child->first(function ($value, $key) {
return $value->id == 121;
});
if (!empty($itm)) {
$childs[] = $itm;
}
}
}*/
            break;
          case 140:
            foreach ($child as $key) {
              if ($key->id == 141) {
                // 121 Tarifario
                if (User::hasPermission(session('user')->email, 'WMT-RRT')) {
                  $childs[] = $key;
                }
              }
              if ($key->id == 148) {
                // 148 Descuentos Financiamiento
                if (User::hasPermission(session('user')->email, 'DMF-RDM')) {
                  $childs[] = $key;
                }
              }
            }
            break;

          case 142:
            //Gestion de fibra
            foreach ($child as $key) {
              if ($key->id == 143) {
                // 143 Ver zonas de fibra
                if (User::hasPermission(session('user')->email, 'FIB-VIW')) {
                  $childs[] = $key;
                }
              }

              if ($key->id == 144) {
                // 144 Cargar mapa de coberturas de fibra
                if (User::hasPermission(session('user')->email, 'FIB-POL')) {
                  $childs[] = $key;
                }
              }
            }
            break;
        }
        $item->childs = $childs;
      }
    }
    return $item;
  }

  public static function getItems()
  {
    $items = array();
    //Usuarios
    if (User::hasPermission(session('user')->email, 'AMU-RUS') ||
      User::hasPermissionPack(session('user')->email, ['LOW']) ||
      User::hasPermission(session('user')->email, 'LOW-SBU') ||
      User::hasPermission(session('user')->email, 'LOW-LRL') ||
      User::hasPermission(session('user')->email, 'USR-LPP') ||
      User::hasPermission(session('user')->email, 'USR-LAP') ||
      User::hasPermission(session('user')->email, 'USR-SCH') ||
      User::hasPermission(session('user')->email, 'DDU-RDU')) {
      $items[] = MainMenuItem::getItem(1);
    }

    //Organizaciones
    if (User::hasPermission(session('user')->email, 'ORG-LST')) {
      $items[] = MainMenuItem::getItem(30);
    }

    //Concentradores
    if (User::hasPermission(session('user')->email, 'AMC-RCO')) {
      $items[] = MainMenuItem::getItem(2);
    }

    //Inventario
    if (User::hasPermissionPack(session('user')->email, ['A1P', 'A1W', 'A2P', 'ADP', 'LDD', 'A1S', 'A1B', 'A3P', 'A1V', 'A2W', 'SRP', 'FIN', 'EIV']) ||
      User::hasPermission(session('user')->email, 'INV-RCL') || User::hasPermission(session('user')->email, 'INV-UMI')) {
      $items[] = MainMenuItem::getItem(3);
    }

    //Vendedores.
    if (User::hasPermission(session('user')->email, 'SEL-DCC') || User::hasPermission(session('user')->email, 'SEL-ASB') || User::hasPermission(session('user')->email, 'SEL-IDD') || User::hasPermission(session('user')->email, 'SEL-DCI') || User::hasPermission(session('user')->email, 'SEL-IDE') || User::hasPermission(session('user')->email, 'SEL-LDC')) {
      $items[] = MainMenuItem::getItem(4);
    }

    //Clientes
    if (User::hasPermissionPack(session('user')->email, ['CLA', 'INV', 'ARV', 'CLI'])) {
      $items[] = MainMenuItem::getItem(28);
    }

    //Obteniendo politicas para reportes asignadas al usuario logueado
    $viewReports = User::hasAnyReport(session('user')->email, 'REP');
    if ($viewReports->count() > 0) {
      $items[] = MainMenuItem::getItem(15, $viewReports);
    }

    //Opcion del menú "brightstar" Apagado en BD
    if (User::hasPermission(session('user')->email, 'REG-BRS')) {
      $items[] = MainMenuItem::getItem(34);
    }

    //Opcion del menú "Reportes BI"
    $viewReportsBI = User::hasAnyReport(session('user')->email, 'RBI');
    if ($viewReportsBI->count() > 0) {
      $items[] = MainMenuItem::getItem(36, $viewReportsBI);
    }

    //Opcion del menú "Reportes Online Sales (Ventas Online)"
    $viewReportsOS = User::hasAnyReport(session('user')->email, 'ROS');
    if ($viewReportsOS->count() > 0) {
      $items[] = MainMenuItem::getItem(50, $viewReportsOS);
    }

    //Opcion del menú "Canales"
    $viewChannels = User::hasAnyReport(session('user')->email, 'CHA');
    if ($viewChannels->count() > 0) {
      $items[] = MainMenuItem::getItem(54, $viewChannels);
    }

    //Opcion del menú "Venta en Abonos"
    $viewInsPay = User::hasAnyReport(session('user')->email, 'ABO');

    if ($viewInsPay->count() > 0) {
      $items[] = MainMenuItem::getItem(68, $viewInsPay);
    }

    //Opcion del menú "Voywey"
    $viewInsVoy = User::hasAnyReport(session('user')->email, 'VOY');
    if ($viewInsVoy->count() > 0) {
      $items[] = MainMenuItem::getItem(97, $viewInsVoy);
    }

    //Opcion del menú "Facturacion"
    $viewInsBil = User::hasAnyReport(session('user')->email, 'BIL');
    if ($viewInsBil->count() > 0) {
      $items[] = MainMenuItem::getItem(103, $viewInsBil);
    }

    //Opcion del menú "Coppel"
    $viewInsBil = User::hasAnyReport(session('user')->email, 'COP');
    if ($viewInsBil->count() > 0) {
      $items[] = MainMenuItem::getItem(108, $viewInsBil);
    }

    //Opcion del menú "Portabilidad"
    if (User::hasPermission(session('user')->email, 'P0R-IMP') ||
      User::hasPermission(session('user')->email, 'P0R-EXP')) {
      $items[] = MainMenuItem::getItem(119);
    }

    // Opción del menú "Gestion Web"
    if (User::hasPermission(session('user')->email, 'WMT-RRT') || User::hasPermission(session('user')->email, 'WMT-CRT') || User::hasPermission(session('user')->email, 'WMT-URT') || User::hasPermission(session('user')->email, 'WMT-DRT') || User::hasPermission(session('user')->email, 'DMF-RDM')) {
      $items[] = MainMenuItem::getItem(140);
    }

    //Gestion de fibra
    if (User::hasPermissionPack(session('user')->email, ['FIB'])) {
      $items[] = MainMenuItem::getItem(142);
    }
/*
$viewPort = User::hasAnyReport(session('user')->email, 'P0R');
if ($viewPort->count() > 0) {
$items[] = MainMenuItem::getItem(119, $viewPort);
}
 */
    return $items;
  }

}
