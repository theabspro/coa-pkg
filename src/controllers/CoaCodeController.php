<?php

namespace Abs\CoaPkg;
use Abs\CoaPkg\CoaCode;
use Abs\CoaPkg\CoaPostingType;
use Abs\CoaPkg\CoaType;
use App\Config;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

// use Validator;

class CoaCodeController extends Controller {

	public function __construct() {
	}

	public function CoaCodeFilter(Request $request) {
		$this->data['extras'] = [
			'currency_code_list' => collect(Config::where('config_type_id', 85)->select('name', 'id')->get())->prepend(['name' => 'Select Currency Code', 'id' => '']),
			'debit_card_proposal_list' => collect(Config::where('config_type_id', 86)->select('name', 'id')->get())->prepend(['name' => 'Select Proposal Type', 'id' => '']),
			'posting_type_list' => collect(CoaPostingType::where('company_id', Auth::user()->company_id)->select('name', 'id')->get())->prepend(['name' => 'Select Posting Type', 'id' => '']),
			'type_list' => collect(CoaType::where('company_id', Auth::user()->company_id)->select('name', 'id')->get())->prepend(['name' => 'Select Type', 'id' => '']),
		];
		$this->data['status_filter'] = array(
			array('name' => "Select Status", 'id' => "0"),
			array('name' => "Active", 'id' => "1"),
			array('name' => "Inactive", 'id' => "2"),
		);
		return response()->json($this->data);
	}

	public function getCoaCodeList(Request $request) {
		//dd($request->all());
		$coa_code = $request->coa_code;
		$coa_code_description = $request->description;

		$coa_code_list = CoaCode::withTrashed()
			->select(
				'coa_codes.*',
				'coa_types.name as coa_type_name',
				'coa_posting_types.name as coa_posting_type_name',
				'configs.name as currency_code'
			)
			->leftjoin('coa_types', 'coa_types.id', 'coa_codes.type_id')
			->leftjoin('coa_posting_types', 'coa_posting_types.id', 'coa_codes.posting_type_id')
			->leftjoin('configs', 'configs.id', 'coa_codes.currency_code_id')
			->where('coa_codes.company_id', Auth::user()->company_id)
			->where(function ($query) use ($coa_code) {
				if ($coa_code != null) {
					$query->where("coa_codes.code", 'like', "%" . $coa_code . "%");
				}
			})
			->where(function ($query) use ($coa_code_description) {
				if ($coa_code_description != null) {
					$query->where("coa_codes.name", 'like', "%" . $coa_code_description . "%");
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->coa_type)) {
					$query->where('coa_codes.type_id', $request->coa_type);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->posting_type)) {
					$query->where('coa_codes.posting_type_id', $request->posting_type);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->currency_code)) {
					$query->where('coa_codes.currency_code_id', $request->currency_code);
				}
			})
			->where(function ($query) use ($request) {
				if (!empty($request->proposal_type)) {
					$query->where('coa_codes.debit_credit_proposal_id', $request->proposal_type);
				}
			});
		if ($request->status != '') {
			$coa_code_list = $coa_code_list->where(function ($query) use ($request) {
				if ($request->status == '1') {
					$query->whereNull('coa_codes.deleted_at');
				} elseif ($request->status == '2') {
					$query->whereNotNull('coa_codes.deleted_at');
				}
			});
		} else {
			$coa_code_list = $coa_code_list->where(function ($query) use ($request) {
				$query->whereNull('coa_codes.deleted_at');
			});
		}

		$coa_code_list = $coa_code_list->groupBy('coa_codes.id')
			->orderBy('coa_codes.id', 'Desc');

		return Datatables::of($coa_code_list)
			->addColumn('code', function ($coa_code_list) {
				if ($coa_code_list->deleted_at == NULL) {
					$code = "<td><span class='status-indicator green'></span>" . $coa_code_list->code . "</td>";
				} else {
					$code = "<td><span class='status-indicator red'></span>" . $coa_code_list->code . "</td>";
				}
				return $code;
			})
			->addColumn('action', function ($coa_code_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/coa-pkg/coa-code/edit/' . $coa_code_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#coa-code-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $coa_code_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->make(true);
	}

	public function getCoaCodeFormdata($id = NULL) {
		$this->data['extras'] = [
			'currency_code_list' => collect(Config::where('config_type_id', 85)->select('name', 'id')->get())->prepend(['name' => 'Select Currency Code', 'id' => '']),
			'debit_card_proposal_list' => collect(Config::where('config_type_id', 86)->select('name', 'id')->get())->prepend(['name' => 'Select Proposal Type', 'id' => '']),
			'posting_type_list' => collect(CoaPostingType::where('company_id', Auth::user()->company_id)->select('name', 'id')->get())->prepend(['name' => 'Select Posting Type', 'id' => '']),
			'type_list' => collect(CoaType::where('company_id', Auth::user()->company_id)->select('name', 'id')->get())->prepend(['name' => 'Select Type', 'id' => '']),
		];
		if ($id == NULL) {
			$coa_code = new CoaCode;
			$this->data['title'] = 'Add Coa Code';
			$this->data['action'] = 'Add';
		} else {
			$this->data['title'] = 'Edit Coa Code';
			$this->data['action'] = 'Edit';
			$coa_code = CoaCode::withTrashed()->where('id', $id)->first();
		}
		$this->data['coa_code'] = $coa_code;

		return response()->json($this->data);
	}

	public function saveCoaCode(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'code.required' => 'COA Code is required',
				'code.unique' => 'COA Code is already taken',
				'name.required' => 'Description is required',
			];

			$validator = Validator::make($request->all(), [
				'code' => [
					'unique:coa_codes,code,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required',
				],
				'name' => 'required',
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			if (empty($request->id)) {
				$coa_code = new CoaCode;
				$msg = "Saved";
				$coa_code->created_by_id = Auth()->user()->id;
			} else {
				$coa_code = CoaCode::withTrashed()->where('id', $request->id)->first();
				$msg = "Updated";
				$coa_code->updated_by_id = Auth()->user()->id;
			}

			$coa_code->company_id = Auth::user()->company_id;
			$coa_code->fill($request->all());
			if ($request->status == 'Active') {
				$coa_code->deleted_at = NULL;
			} else {
				$coa_code->deleted_at = date('Y-m-d H:i:s');
				$coa_code->deleted_by_id = Auth::user()->id;
			}
			$coa_code->save();

			DB::commit();
			return response()->json(['success' => true, 'comes_from' => $msg]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteCoaCode($id) {
		DB::beginTransaction();
		try {
			$coa_code_delete = CoaCode::withTrashed()->where('id', $id)->first();
			$coa_code_delete->forceDelete();

			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

}
