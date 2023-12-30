<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Pack;
use App\Inventory;
use App\User;

class AssignedPack extends Model {
	protected $table = 'islim_assig_packs';

	protected $fillable = [
		'id', 'inv_arti_details_id', 'users_email', 'packs_id'
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
            $obj = new AssignedPack;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getAssignedPack ($id, $statusPack, $statusProduct, $statusUser) {
        $assignedpack = AssignedPack::where(['id' => $id])->first();
        if (isset($assignedpack)) {
            $assignedpack->user = User::where(['email' => $assignedpack->users_email, 'status' => $statusPack])->first();
            $assignedpack->article = Inventory::getArticle($assignedpack->inv_arti_details_id, $statusProduct);
            $assignedpack->pack = Pack::where(['id' => $assignedpack->packs_id, 'status' => $statusUser])->first();
        }
        return $assignedpack;
    }

    public static function getAssignedPackByInventoryPack ($idInventory, $idPack, $statusPack, $statusProduct, $statusUser) {
        $assignedpack = AssignedPack::where(['inv_arti_details_id' => $idInventory, 'packs_id' => $idPack])->first();
        $assignedpack->user = User::where(['email' => $assignedpack->users_email, 'status' => $statusPack])->first();
        $assignedpack->article = Inventory::getArticle($assignedpack->inv_arti_details_id, $statusProduct);
        $assignedpack->pack = Pack::where(['id' => $assignedpack->packs_id, 'status' => $statusUser])->first();
        return $assignedpack;
    }
}