<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AssignedSaleDetails extends Model {
	protected $table = 'islim_asigned_sale_details';

	protected $fillable = [
		'id', 'asigned_sale_id', 'amount', 'amount_text', 'unique_transaction'
    ];

    public $timestamps = false;

     /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     *
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new AssignedSaleDetails;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getAssignedSaleDetails ($id) {
        $sale = AssignedSaleDetails::find($id);
        if (isset($sale))
            $sale->assignedSale = AssignedSales::find($sale->asigned_sale_id);
        return $sale;
    }
}