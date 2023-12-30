<?php

Route::get('view/installments/config', 'InstallmentsController@configView')
	   ->name('installments.config');

Route::post('api/installments/config/save', 'InstallmentsController@configSave')
	   ->name('installments.configSave');

Route::get('view/installments/assigned', 'InstallmentsController@assignedView')
	   ->name('installments.assigned');

Route::post('api/installments/assigned-coord', 'InstallmentsController@assignedCoord')
	   ->name('installments.assignedCoord');

Route::post('api/installments/find-coord', 'InstallmentsController@findUser')
	   ->name('installments.findUser');

Route::post('api/installments/consult-coord', 'InstallmentsController@consultCoordinador')
	   ->name('installments.consultCoordinador');