<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Organization;
use App\Deactive;
//use App\APIKey;
//use App\ClientNetwey;
//use App\CoordinateChanges;
//use App\Inventory;
//use App\Pack;
//use App\Product;
//use App\Service;
//

class SaleMetrics extends Model
{
  protected $table = 'islim_sales';

  protected $fillable = [
    'services_id',
    'concentrators_id',
    'assig_pack_id',
    'inv_arti_details_id',
    'api_key',
    'users_email',
    'packs_id',
    'order_altan',
    'unique_transaction',
    'codeAltan',
    'type',
    'id_point',
    'description',
    'amount',
    'amount_net',
    'com_amount',
    'msisdn',
    'conciliation',
    'lat',
    'lng',
    'position',
    'date_reg',
    'sale_type',
    'status'
  ];

  public $timestamps = false;

  /**
   * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
   * @param String $typeCon
   *
   * @return App\SaleMetrics
   */
  public static function getConnect($typeCon = false)
  {
    if ($typeCon) {
      $obj = new SaleMetrics;
      $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

      return $obj;
    }
    return null;
  }

  public static function getTotalSalesMD($dateB = false, $dateE = false, $typeS = false, $typeD = false, $org = false){
    if($typeS){
      $cv = DB::raw('COUNT(islim_sales.id) as total_u');
      $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

      $data = self::getConnect('R')
                    ->select($cv, $ta)
                    ->join(
                      'islim_client_netweys',
                      'islim_client_netweys.msisdn',
                      'islim_sales.msisdn'
                    )
                    ->join(
                      'islim_clients',
                      'islim_client_netweys.clients_dni',
                      'islim_clients.dni'
                    )
                    ->where([
                        ['islim_clients.name', '!=', 'TEMPORAL'],
                        //['islim_clients.last_name', '!=', 'TEMPORAL'],
                        ['islim_sales.type', $typeS]
                    ])
                    ->whereIn('islim_client_netweys.status', ['A', 'S'])
                    ->whereIn('islim_sales.status', ['A', 'E']);
      
      if($org != 'wo'){
        $data->join(
                      'islim_users',
                      'islim_users.email',
                      'islim_sales.users_email'
                    );
      }

      if($dateB){
        $data->where('islim_sales.date_reg', '>=', $dateB);
      }

      if($dateE){
        $data->where('islim_sales.date_reg', '<=', $dateE);
      }

      if($typeD){
        if($typeD == 'MH_M'){
          $data->where('islim_sales.sale_type', 'MH' );  
          $data->where([
                        ['islim_sales.is_migration', 'Y']   
                    ]);
        }elseif($typeD == 'F'){
          $data->where('islim_sales.sale_type', 'F'); 
        }else{
          $data->where('islim_sales.sale_type', $typeD);
          $data->where([
                        ['islim_sales.is_migration', 'N']   
                    ]);  
        }   
      }

      if($org){
        $data->where('islim_users.id_org', $org);
      }elseif($org === false){
        $data->whereNull('islim_users.id_org');
      }
      
      return $data->first();
    }

    return null;
  }

  public static function getTotalRecharge($typeD = false, $org = false){
    $cv = DB::raw('COUNT(islim_sales.id) as total_u');
    $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

    $data = self::getConnect('R')
                  ->select($cv, $ta)
                  ->join(
                    'islim_client_netweys',
                    'islim_client_netweys.msisdn',
                    'islim_sales.msisdn'
                  )
                  ->where([
                    ['islim_sales.type', 'R'],
                    ['islim_sales.status', 'A']
                  ])
                  ->whereIn('islim_client_netweys.status', ['A', 'S']);

    if($org){
      $data->join(
                    'islim_users',
                    'islim_users.email',
                    'islim_sales.users_email'
                  );

      $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
      $data->whereIn('islim_users.id_org', $orgs->pluck('id'));
    }

    if($typeD){
      $data->where('islim_sales.sale_type', $typeD);
    }

    return $data->first();
  }

  public static function getTotalSales($dateB = false, $dateE = false, $typeS = false, $typeD = false, $org = false, $separe_MH_MHM = false){
    if($typeS){
      $cv = DB::raw('COUNT(islim_sales.id) as total_u');
      $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

      $data = self::getConnect('R')
                    ->select($cv, $ta)
                    ->join(
                      'islim_client_netweys',
                      'islim_client_netweys.msisdn',
                      'islim_sales.msisdn'
                    )
                    ->join(
                      'islim_clients',
                      'islim_client_netweys.clients_dni',
                      'islim_clients.dni'
                    )
                    ->where([
                        ['islim_clients.name', '!=', 'TEMPORAL'],
                        //['islim_clients.last_name', '!=', 'TEMPORAL'],
                        ['islim_sales.type', $typeS]
                    ])
                    //->whereIn('islim_client_netweys.status', ['A', 'S'])
                    ->where(function($q){
                      $q->whereIn('islim_client_netweys.status',['A', 'S'])
                        ->orWhereIn('islim_client_netweys.msisdn', Deactive::select('msisdn')->where([['status', 'A']]));
                    })
                    ->whereIn('islim_sales.status', ['A', 'E']);

      if($org != 'wo'){
        $data->join(
                      'islim_users',
                      'islim_users.email',
                      'islim_sales.users_email'
                    );
      }

      if($dateB){
        $data->where('islim_sales.date_reg', '>=', $dateB);
      }

      if($dateE){
        $data->where('islim_sales.date_reg', '<=', $dateE);
      }

      if($typeD){
        if($typeD == 'MH_M'){
          $data->where('islim_sales.sale_type', 'MH');
          $data->where([
                        ['islim_sales.is_migration', 'Y']   
                    ]); 
        }else{
          $data->where('islim_sales.sale_type', $typeD); 
          if(!$separe_MH_MHM){
            if($typeD != 'F'){
              $data->where([
                          ['islim_sales.is_migration', 'N']
                      ]);
            }
          }   
        }    
      }

      if($org && $org != 'wo'){
        $orgs = Organization::getOrgsPermitByOrgs(session('user.id_org'));
        $data->whereIn('islim_users.id_org', $orgs->pluck('id'));
      }elseif($org === false){
        $data->whereNull('islim_users.id_org');
      }

      return $data->first();
    }

    return null;
  }

  public static function getTotalSalesV($dateB = false, $dateE = false, $typeD = false, $org = false){
    if($dateB && $dateE){
      if(!empty($org)){
        $sub = DB::raw(
                '(SELECT id FROM islim_sales as b
                  WHERE (b.status = "A" OR b.status = "E") AND
                  b.type = "P" AND
                  b.unique_transaction = islim_sales.unique_transaction AND
                  islim_users.id_org = '.$org.')');
      }else{
        $sub = DB::raw(
                '(SELECT id FROM islim_sales as b
                  WHERE (b.status = "A" OR b.status = "E") AND
                  b.type = "P" AND
                  b.unique_transaction = islim_sales.unique_transaction)');
      }

      $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

      $data = self::getConnect('R')
                    ->select($ta)
                    ->join(
                      'islim_client_netweys',
                      'islim_client_netweys.msisdn',
                      'islim_sales.msisdn'
                    )
                    ->join(
                      'islim_clients',
                      'islim_client_netweys.clients_dni',
                      'islim_clients.dni'
                    )
                    ->where([
                        ['islim_clients.name', '!=', 'TEMPORAL'],
                        //['islim_clients.last_name', '!=', 'TEMPORAL'],
                        ['islim_client_netweys.status','A'],
                        ['islim_sales.date_reg', '>=', $dateB],
                        ['islim_sales.date_reg', '<=', $dateE],
                        ['islim_sales.type', 'V']
                    ])
                    ->whereIn('islim_sales.status', ['A', 'E'])
                    ->whereNotNull($sub);

      if($org != 'wo'){
        $data->join(
                      'islim_users',
                      'islim_users.email',
                      'islim_sales.users_email'
                    );
      }

      if(!empty($typeD)){
        if($typeD == 'MH_M'){
          $data->where('islim_sales.sale_type', 'MH'); 
          $data->where([
                          ['islim_sales.is_migration', 'Y']   
                      ]); 
        }elseif($typeD == 'F'){
          $data->where('islim_sales.sale_type', 'F'); 
        }else{
          $data->where('islim_sales.sale_type', $typeD); 
          $data->where([
                        ['islim_sales.is_migration', 'N']   
                    ]); 
        } 
      }

      if(!empty($org)){
        $data->where('islim_users.id_org', $org);
      }

      return $data->first();
    }

    return null;
  }

  public static function getTotalRechargeWO($dateB = false, $dateE = false, $typeD = false){
    if($dateB && $dateE){
      $cv = DB::raw('COUNT(islim_sales.id) as total_u');
      $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

      $data = self::getConnect('R')
                  ->select($cv, $ta)
                  ->leftJoin(
                      'islim_users',
                      'islim_users.email',
                      'islim_sales.users_email'
                  )
                  ->where([
                    ['islim_sales.date_reg', '>=', $dateB],
                    ['islim_sales.date_reg', '<=', $dateE],
                    ['islim_sales.type', 'R']
                  ])
                  ->whereIn('islim_sales.status', ['A', 'E'])
                  ->where(function($wh){
                    $wh->whereNull('islim_sales.users_email')
                       ->orWhereNull('islim_users.id_org');
                  });
                  /*->whereNull('islim_sales.users_email');
                  $data->whereNull('islim_users.id_org');*/

      if(!empty($typeD)){
        if($typeD == 'MH_M'){
          $data->where('islim_sales.sale_type', 'MH');
         
        }else{
          $data->where('islim_sales.sale_type', $typeD);  
        } 
      }

      return $data->first();
    }

    return null;
  }

  public static function getTotalSalesWO($dateB = false, $dateE = false, $typeD = false){
    if($dateB && $dateE){
      $ta = DB::raw('SUM(islim_sales.amount) as total_mount');

      $sub = DB::raw('(SELECT id FROM islim_sales as b WHERE (b.status = "A" OR b.status = "E") AND b.type = "P" AND b.unique_transaction = islim_sales.unique_transaction AND islim_users.id_org IS NULL)');

      $data = self::getConnect('R')
                  ->select($ta)
                  ->join(
                    'islim_users',
                    'islim_users.email',
                    'islim_sales.users_email'
                  )
                  ->where([
                    ['islim_sales.date_reg', '>=', $dateB],
                    ['islim_sales.date_reg', '<=', $dateE],
                    ['islim_sales.type', 'V']
                  ])
                  ->whereIn('islim_sales.status', ['A', 'E'])
                  ->whereNull('islim_users.id_org')
                  ->whereNotNull($sub);

      if(!empty($typeD)){
        $data->where('islim_sales.sale_type', $typeD);
      }

      return $data->first();
    }

    return null;
  }
}