<?php

Route::group(['namespace' => 'Abs\CoaPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'coa-pkg'], function () {
	//COA TYPE
	Route::get('/coa-types/get-list', 'CoaTypeController@getCoaTypeList')->name('getCoaTypeList');
	Route::post('/coa-type/save', 'CoaTypeController@saveCoaType')->name('saveCoaType');
	Route::get('/coa-type/get-form-data/{id?}', 'CoaTypeController@getCoaTypeFormdata')->name('getCoaTypeFormdata');
	Route::get('/coa-type/delete/{id?}', 'CoaTypeController@deleteCoaType')->name('deleteCoaType');

	//COA POSTING TYPE
	Route::get('/coa-posting-types/get-list', 'CoaPostingTypeController@getCoaPostingTypeList')->name('getCoaPostingTypeList');
	Route::post('/coa-posting-type/save', 'CoaPostingTypeController@saveCoaPostingType')->name('saveCoaPostingType');
	Route::get('/coa-posting-type/get-form-data/{id?}', 'CoaPostingTypeController@getCoaPostingTypeFormdata')->name('getCoaPostingTypeFormdata');
	Route::get('/coa-posting-type/delete/{id?}', 'CoaPostingTypeController@deleteCoaPostingType')->name('deleteCoaPostingType');

	//COA CODE
	Route::get('/coa-codes/get-list', 'CoaCodeController@getCoaCodeList')->name('getCoaCodeList');
	Route::get('/coa-code/save', 'CoaCodeController@saveCoaCode')->name('saveCoaCode');
});