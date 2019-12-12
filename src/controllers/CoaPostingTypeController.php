<?php

namespace Abs\CoaPkg;
use Abs\CoaPkg\CoaPostingType;
use App\Http\Controllers\Controller;
use Auth;
use DB;
use Illuminate\Http\Request;
use Validator;
use Yajra\Datatables\Datatables;

class CoaPostingTypeController extends Controller {

	public function __construct() {
	}

	public function getCoaPostingTypeList() {
		$coa_posting_type_list = CoaPostingType::withTrashed()
			->select(
				'coa_posting_types.*'
				//DB::raw('IF(coa_posting_types.deleted_at IS NULL, "ACTIVE", "INACTIVE") as status')
			)
			->where('coa_posting_types.company_id', Auth::user()->company_id)
			->groupBy('coa_posting_types.id')
			->orderBy('coa_posting_types.id', 'Desc');

		return Datatables::of($coa_posting_type_list)
			->addColumn('name', function ($coa_posting_type_list) {
				if ($coa_posting_type_list->deleted_at == NULL) {
					$name = "<td><span class='status-indicator green'></span>" . $coa_posting_type_list->name . "</td>";
				} else {
					$name = "<td><span class='status-indicator red'></span>" . $coa_posting_type_list->name . "</td>";
				}
				return $name;
			})
			->addColumn('action', function ($coa_posting_type_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/coa-pkg/coa-posting-type/edit/' . $coa_posting_type_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#coa-posting-type-delete-modal" onclick="angular.element(this).scope().calldeleteConfirm(' . $coa_posting_type_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->make(true);
	}

	public function getCoaPostingTypeFormdata($id = NULL) {

		if ($id == NULL) {
			$coa_posting_types = [];
			$this->data['title'] = 'Add Coa Posting Type';
			$this->data['action'] = 'Add';
		} else {
			$this->data['title'] = 'Edit Coa Posting Type';
			$this->data['action'] = 'Edit';
			$coa_posting_types = CoaPostingType::withTrashed()->where('id', $id)->get();
		}
		$this->data['coa_posting_types'] = $coa_posting_types;

		return response()->json($this->data);
	}

	public function saveCoaPostingType(Request $request) {
		//dd($request->all());
		try {
			if (isset($request->coa_posting_types) && !empty($request->coa_posting_types)) {
				$error_messages = [
					'name.required' => 'COA Posting Type name is required',
					'name.unique' => 'COA Posting Type name is already taken',
				];

				foreach ($request->coa_posting_types as $coa_posting_type_key => $coa_posting_type) {
					$validator = Validator::make($coa_posting_type, [
						'name' => [
							'unique:coa_posting_types,name,' . $coa_posting_type['id'] . ',id,company_id,' . Auth::user()->company_id,
							'required:true',
						],
					], $error_messages);

					if ($validator->fails()) {
						return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
					}

					//FIND DUPLICATE COA POSTING TYPES
					foreach ($request->coa_posting_types as $search_key => $search_array) {
						if ($search_array['name'] == $coa_posting_type['name']) {
							if ($search_key != $coa_posting_type_key) {
								return response()->json(['success' => false, 'errors' => ['COA Posting type name already taken']]);
							}
						}
					}
				}
				//}

				//DELETE COA-CODES
				DB::beginTransaction();
				if (!empty($request->coa_posting_type_removal_ids)) {
					$coa_posting_type_removal_ids = json_decode($request->coa_posting_type_removal_ids, true);
					$coa_posting_type_delete = CoaPostingType::withTrashed()->whereIn('id', $coa_posting_type_removal_ids)->forcedelete();
				}

				//if (isset($request->coa_posting_types) && !empty($request->coa_posting_types)) {
				foreach ($request->coa_posting_types as $key => $coa_posting_type) {
					$coa_posting_type_save = CoaPostingType::withTrashed()->firstOrNew(['id' => $coa_posting_type['id']]);
					$coa_posting_type_save->company_id = Auth::user()->company_id;
					$coa_posting_type_save->fill($coa_posting_type);
					if ($coa_posting_type['status'] == 'Active') {
						$coa_posting_type_save->deleted_at = NULL;
					} else {
						$coa_posting_type_save->deleted_at = date('Y-m-d H:i:s');
						$coa_posting_type_save->deleted_by_id = Auth::user()->id;
					}
					if (empty($coa_posting_type['id'])) {
						$msg = "Saved";
						$coa_posting_type_save->created_by_id = Auth()->user()->id;
					} else {
						$msg = "Updated";
						$coa_posting_type_save->updated_by_id = Auth()->user()->id;
					}
					$coa_posting_type_save->save();
				}
				DB::commit();
				return response()->json(['success' => true, 'comes_from' => $msg]);
			} else {
				if (!empty($request->coa_posting_type_removal_ids)) {
					$coa_posting_type_removal_ids = json_decode($request->coa_posting_type_removal_ids, true);
					$coa_posting_type_delete = CoaPostingType::withTrashed()->whereIn('id', $coa_posting_type_removal_ids)->forcedelete();
					$msg = "Updated";
					return response()->json(['success' => true, 'comes_from' => $msg]);
				}
				return response()->json(['success' => true, 'comes_from' => '']);
			}

		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

	public function deleteCoaPostingType($id) {
		DB::beginTransaction();
		try {
			$coa_posting_type_delete = CoaPostingType::withTrashed()->where('id', $id)->first();
			$coa_posting_type_delete->forceDelete();

			DB::commit();
			return response()->json(['success' => true]);
		} catch (Exception $e) {
			DB::rollBack();
			return response()->json(['success' => false, 'errors' => ['Exception Error' => $e->getMessage()]]);
		}
	}

}