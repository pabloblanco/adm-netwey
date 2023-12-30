<?php
namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class HelpersS3{

	static $dir = [
	'DepositSeller' => '/PlatformAdministrator/deposit/seller/',
	'DepositConcentrator' => '/PlatformAdministrator/deposit/concentrator/',
	'DepositDealer' => '/PlatformAdministrator/deposit/dealer/',
	];

	public static function insertImage($name, $dir, $file, $visibility='private'){
		$stor = Storage::disk('s3')->put(self::$dir[$dir].=$name, base64_decode($file), $visibility);
		if ($stor){
			return 1;
		}
		
	}
}