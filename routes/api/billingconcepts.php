<?php

Route::post('api/billingconcepts/list-dt', 'BillingConceptController@listDT');

Route::post('api/billingconcepts/store', 'BillingConceptController@store');

Route::put('api/billingconcepts/{id}', 'BillingConceptController@update');