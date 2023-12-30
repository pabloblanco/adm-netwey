<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Product;

class ArticlePack extends Model
{
    protected $table = 'islim_arti_packs';

	protected $fillable = [
        'pack_id', 'inv_article_id', 'retail', 'status'
    ];

    protected $primaryKey = 'id';

    public $incrementing = false;

    public $timestamps = false;

    /**
     * Metodo para seleccionar conexion a la bd, escritura-lectura o solo escritura
     * @param String $typeCon
     * 
     * @return App\Product
    */
    public static function getConnect($typeCon = false){
        if($typeCon){
            $obj = new ArticlePack;
            $obj->setConnection($typeCon == 'W' ? 'netwey-w' : 'netwey-r');

            return $obj;
        }
        return null;
    }

    public static function getPacksIdByartic($artic = false){
        if($artic){
            return self::getConnect('R')
                        ->select('pack_id')
                        ->where([
                            ['status', 'A'],
                            ['inv_article_id', $artic]
                        ])
                        ->get();
        }

        return [];
    }

    public static function getProductsByPack ($id, $status) {
        $productsId = ArticlePack::select('inv_article_id', 'retail')->where(['pack_id' => $id, 'status' => $status])->get();
        $products = array();
        foreach ($productsId as $ids) {
            $p = Product::getProduct($ids->inv_article_id);
            $p->retail = $ids->retail;
            $products[] = $p;
        }
        return $products;
    }

    public static function areMoreProductsRetail($product = false){
        if($product){
            return ArticlePack::select('id')
                       ->where([
                            ['inv_article_id', $product],
                            ['retail', 'Y'],
                            ['status', 'A']
                        ])
                       ->get()
                       ->count();
        }
        return false;
    }

}