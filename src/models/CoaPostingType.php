<?php

namespace Abs\CoaPkg;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaPostingType extends Model {
	use SoftDeletes;
	protected $table = 'coa_posting_types';
	protected $fillable = [
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

}
