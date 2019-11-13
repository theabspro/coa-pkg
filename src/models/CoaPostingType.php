<?php

namespace Abs\CoaPkg;

use App\Company;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaPostingType extends Model {
	use SoftDeletes;
	protected $table = 'coa_posting_types';
	protected $fillable = [
		'company_id',
		'name',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];
	protected $appends = ['switch_value'];

	public function getSwitchValueAttribute() {
		return !empty($this->attributes['deleted_at']) ? 'Inactive' : 'Active';
	}

	public function coaCodes() {
		return $this->hasMany('Abs\CoaPkg\CoaCode', 'posting_type_id', 'id');
	}

	public static function createFromObject($record_data) {

		$errors = [];
		$company = Company::where('code', $record_data->company)->first();
		if (!$company) {
			dump('Invalid Company : ' . $record_data->company);
			return;
		}

		$admin = $company->admin();
		if (!$admin) {
			dump('Default Admin user not found');
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'name' => $record_data->coa_posting_type_name,
		]);
		$record->created_by_id = $admin->id;
		$record->save();
		return $record;
	}

	public static function createFromCollection($records) {
		foreach ($records as $key => $record_data) {
			try {
				if (!$record_data->company) {
					continue;
				}
				$record = self::createFromObject($record_data);
			} catch (Exception $e) {
				dd($e);
			}
		}
	}

}
