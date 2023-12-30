<?php
 
namespace App\Utilities;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
class Utils {
	public static function getSideBarOptions () {
		$sideBarRoles = array();
		$idRole = 0;
		foreach (session('user')->policies as $key => $policy) {
			if (($policy->code == 'LUS-U5U') || //Accede a usuarios
                ($policy->code == 'LEC-MC0') || //Accede a concentradores
                ($policy->code == 'RCL-MV3') || //Accede a clientes
                ($policy->code == 'LEP-M1N') || //Accede a proveedores
                ($policy->code == 'CB0-M1N') || //Accede a almacenes o bodegas
                ($policy->code == 'A1B-M1N') || //Accede a inventarios
                ($policy->code == 'ADB-M1N') && ($policy->value > 0)//Accede a inventarios por vendedor
            ) {
            	if ($idRole != $policy->roles_id) {
            		$idRole = $policy->roles_id;
					$roles = Role::getRole($policy->roles_id);
					foreach($roles as $role) {
						$sideBarRoles[] = $role;
					}
            	}
			}
		}
		return $sideBarRoles;
	}
}