<?php

Route::group(['namespace' => 'Abs\CoaPkg', 'middleware' => ['web', 'auth'], 'prefix' => 'coa-pkg'], function () {
	//COA TYPE
	Route::get('/coa-types/get-list', 'CoaTypeController@getCoaTypeList')->name('getCoaTypeList');
	Route::get('/coa-type/save', 'CoaTypeController@saveCoaType')->name('saveCoaType');

	//COA POSTING TYPE
	Route::get('/coa-posting-types/get-list', 'CoaPostingTypeController@getCoaPostingTypeList')->name('getCoaPostingTypeList');
	Route::get('/coa-posting-type/save', 'CoaPostingTypeController@saveCoaPostingType')->name('saveCoaPostingType');

	//COA CODE
	Route::get('/coa-codes/get-list', 'CoaCodeController@getCoaCodeList')->name('getCoaCodeList');
	Route::get('/coa-code/save', 'CoaCodeController@saveCoaCode')->name('saveCoaCode');
});