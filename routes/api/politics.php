<?php

//Obtener usuarios por perfil
Route::post('api/politics/get-users-by-profile', 'PoliticsController@getUsersByProfile');


/* ========== Politicas predeterminadas ==========*/
//Listar politicas por perfil
Route::get('view/politics', 'PoliticsController@view');
//Actualiza politicas por perfil
Route::post('api/politics/update-politics-profile', 'PoliticsController@update');



/* ========== Asignacion de politicas masivas ========== */
//Obtener vista
Route::get('view/assign-massive-politics/', 'PoliticsController@viewMassivePolitics');
//Actualizar politicas masivamente a usuarios
Route::post('api/politics/edit-policy-users', 'PoliticsController@massiveEditPolicies');






