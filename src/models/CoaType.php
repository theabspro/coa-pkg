<?php

namespace Abs\CoaPkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaType extends Model {
	use SoftDeletes;
	protected $table = 'coa_types';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
