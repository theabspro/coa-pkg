<?php

namespace Abs\CoaPkg;

use App\Company;
use App\Config;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CoaCode extends Model {
	use SoftDeletes;
	protected $table = 'coa_codes';
	protected $fillable = [
		'company_id',
		'code',
		'name',
		'currency_code_id',
		'type_id',
		'debit_credit_proposal_id',
		'posting_type_id',
		'created_by_id',
		'updated_by_id',
		'deleted_by_id',
	];

	public function coaTypes() {
		return $this->belongsTo('Abs\CoaPkg\CoaType')->withTrashed();
	}

	public function coaPostingTypes() {
		return $this->belongsTo('Abs\CoaPkg\CoaPostingType')->withTrashed();
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

		if (!$record_data->currency_code) {
			$currency_code_id = NULL;
		} else {
			$currency_code = Config::where('name', $record_data->currency_code)->where('config_type_id', 85)->first();
			if (!$currency_code) {
				$errors[] = 'Invalid currency code : ' . $record_data->currency_code;
			}
			$currency_code_id = $currency_code->id;
		}

		$type = CoaType::where('name', $record_data->type)->where('company_id', $company->id)->first();
		if (!$type) {
			$errors[] = 'Invalid type : ' . $record_data->type;
		}

		if (!$record_data->debit_credit_proposal) {
			$proposal_id = NULL;
		} else {
			$proposal = Config::where('name', $record_data->debit_credit_proposal)->where('config_type_id', 86)->first();
			if (!$proposal) {
				$errors[] = 'Invalid debit credit proposal : ' . $record_data->debit_credit_proposal;
			}
			$proposal_id = $proposal->id;
		}

		$posting_type = CoaPostingType::where('name', $record_data->posting_type)->where('company_id', $company->id)->first();
		if (!$posting_type) {
			$errors[] = 'Invalid posting type : ' . $record_data->posting_type;
		}

		if (count($errors) > 0) {
			dump($errors);
			return;
		}

		$record = self::firstOrNew([
			'company_id' => $company->id,
			'code' => $record_data->coa_code,
		]);
		$record->name = $record_data->name;
		$record->currency_code_id = $currency_code_id;
		$record->type_id = $type->id;
		$record->debit_credit_proposal_id = $proposal_id;
		$record->posting_type_id = $posting_type->id;
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
				dump($e);
			}
		}
	}

}
