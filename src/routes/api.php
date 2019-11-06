<?php
Route::group(['namespace' => 'Abs\CoaPkg\Api', 'middleware' => ['api']], function () {
	Route::group(['prefix' => 'coa-pkg/api'], function () {
		Route::group(['middleware' => ['auth:api']], function () {
		});
	});
});