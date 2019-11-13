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

class CoaTypeController extends Controller {

	public function __construct() {
	}

	public function getCoaTypeList() {
		$coa_type_list = CoaType::withTrashed()
			->select(
				'coa_types.*',
				//DB::raw('IF(coa_types.deleted_at IS NULL, "ACTIVE", "INACTIVE") as status')
				DB::raw('count(coa_codes.id) as coa_code_names')
			)
			->leftjoin('coa_codes', 'coa_codes.type_id', 'coa_types.id')
			->where('coa_types.company_id', Auth::user()->company_id)
			->groupBy('coa_types.id')
			->orderBy('coa_types.id', 'Desc');

		return Datatables::of($coa_type_list)
			->addColumn('name', function ($coa_type_list) {
				if ($coa_type_list->deleted_at == NULL) {
					$name = "<td><span class='status-indicator green'></span>" . $coa_type_list->name . "</td>";
				} else {
					$name = "<td><span class='status-indicator red'></span>" . $coa_type_list->name . "</td>";
				}
				return $name;
			})
			->addColumn('action', function ($coa_type_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/coa-pkg/coa-type/edit/' . $coa_type_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#coa-type-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $coa_type_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->make(true);
	}

	public function getCoaTypeFormdata($id = NULL) {

		$this->data['extras'] = [
			'currency_code_list' => collect(Config::where('config_type_id', 85)->select('name', 'id')->get())->prepend(['name' => 'Select Currency Code', 'id' => '']),
			'debit_card_proposal_list' => collect(Config::where('config_type_id', 86)->select('name', 'id')->get())->prepend(['name' => 'Select Debit Card Proposal', 'id' => '']),
			'posting_type_list' => collect(CoaPostingType::where('company_id', Auth::user()->company_id)->select('name', 'id')->get())->prepend(['name' => 'Select Posting Type', 'id' => '']),
		];
		if ($id == NULL) {
			$coa_type = new CoaType;
			$coa_type->coa_codes = [];
			$this->data['title'] = 'Add Coa Type';
			$this->data['action'] = 'Add';
		} else {
			$this->data['title'] = 'Edit Coa Type';
			$this->data['action'] = 'Edit';
			$coa_type = CoaType::withTrashed()->where('id', $id)->with([
				'coaCodes',
			])
				->first();
		}
		$this->data['coa_type'] = $coa_type;

		return response()->json($this->data);
	}

	public function saveCoaType(Request $request) {
		// dd($request->all());
		DB::beginTransaction();
		try {

			$error_messages = [
				'name.required' => 'COA Type name is required',
				'name.unique' => 'COA Type name is already taken',
			];

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:coa_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required',
				],
			], $error_messages);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			//VALIDATE UNIQUE FOR COA-CODE
			if (isset($request->coa_codes) && !empty($request->coa_codes)) {
				$error_messages_1 = [
					'code.required' => 'COA Code is required',
					'code.unique' => 'COA Code is already taken',
				];

				foreach ($request->coa_codes as $coa_code) {
					$validator_1 = Validator::make($coa_code, [
						'code' => [
							'unique:coa_codes,code,' . $coa_code['id'] . ',id,company_id,' . Auth::user()->company_id,
							'required',
						],
					], $error_messages_1);

					if ($validator_1->fails()) {
						return response()->json(['success' => false, 'errors' => $validator_1->errors()->all()]);
					}
				}
			}

			if (empty($request->id)) {
				$coa_type = new CoaType;
				$msg = "Saved";
				$coa_type->created_by_id = Auth()->user()->id;
			} else {
				$coa_type = CoaType::withTrashed()->where('id', $request->id)->first();
				$msg = "Updated";
				$coa_type->updated_by_id = Auth()->user()->id;
			}

			$coa_type->company_id = Auth::user()->company_id;
			$coa_type->name = $request->name;
			if ($request->status == 'Active') {
				$coa_type->deleted_at = NULL;
			} else {
				$coa_type->deleted_at = date('Y-m-d H:i:s');
				$coa_type->deleted_by_id = Auth::user()->id;
			}
			$coa_type->save();

			//DELETE COA-CODES
			if (!empty($request->coa_code_removal_ids)) {
				$coa_code_removal_ids = json_decode($request->coa_code_removal_ids, true);
				CoaCode::withTrashed()->whereIn('id', $coa_code_removal_ids)->forcedelete();
			}

			if (isset($request->coa_codes) && !empty($request->coa_codes)) {
				foreach ($request->coa_codes as $key => $coa_code) {
					$coa_code_save = CoaCode::withTrashed()->firstOrNew(['id' => $coa_code['id']]);
					$coa_code_save->company_id = Auth::user()->company_id;
					$coa_code_save->fill($coa_code);
					$coa_code_save->type_id = $coa_type->id;
					if (empty($coa_code['id'])) {
						$coa_code_save->created_by_id = Auth::user()->id;
					} else {
						$coa_code_save->updated_by_id = Auth::user()->id;
					}
					$coa_code_save->save();
				}
			}

			DB::commit();
			return response()->json(['success' => true, 'comes_from' => $msg]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteCoaType($id) {
		DB::beginTransaction();
		try {
			$coa_type_delete = CoaType::withTrashed()->where('id', $id)->first();
			$coa_type_delete->forceDelete();

			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

}