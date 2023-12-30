<?php
/*rutas CRUD usuario*/

Route::post('api/organization/store', 'OrganizationController@store');

Route::post('api/organization/update/{rfc}', 'OrganizationController@update');

Route::delete('api/organization/{rfc}', 'OrganizationController@delete');

//Route::post('api/organization/responsible', 'OrganizationController@assignResponsible');

Route::get('api/organization/get/datatable', 'OrganizationController@getorganizationdt');

Route::get('api/organization/get/responsible/{rfc}', 'OrganizationController@resposible');