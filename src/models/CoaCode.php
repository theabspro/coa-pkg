<?php

namespace Abs\CoaPkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaCode extends Model {
	use SoftDeletes;
	protected $table = 'coa_codes';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
