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
			->addColumn('status', function ($coa_posting_type_list) {
				if ($coa_posting_type_list->deleted_at == NULL) {
					$status = "<td><span class='status-indicator green'></span>ACTIVE</td>";
				} else {
					$status = "<td><span class='status-indicator red'></span>INACTIVE</td>";
				}
				return $status;
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
			$coa_posting_type = new CoaPostingType;
			$this->data['title'] = 'Add Coa Posting Type';
			$this->data['action'] = 'Add';
		} else {
			$this->data['title'] = 'Edit Coa Posting Type';
			$this->data['action'] = 'Edit';
			$coa_posting_type = CoaPostingType::withTrashed()->where('id', $id)->first();
		}
		$this->data['coa_posting_type'] = $coa_posting_type;

		return response()->json($this->data);
	}

	public function saveCoaPostingType(Request $request) {
		//dd($request->all());
		DB::beginTransaction();
		try {

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:coa_posting_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required:true',
				],
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
			}

			if (empty($request->id)) {
				$coa_posting_type = new CoaPostingType;
				$msg = "Saved";
				$coa_posting_type->created_by_id = Auth()->user()->id;
			} else {
				$coa_posting_type = CoaPostingType::withTrashed()->where('id', $request->id)->first();
				$msg = "Updated";
				$coa_posting_type->updated_by_id = Auth()->user()->id;
			}

			$coa_posting_type->company_id = Auth::user()->company_id;
			$coa_posting_type->name = $request->name;
			if ($request->status == 'Active') {
				$coa_posting_type->deleted_at = NULL;
			} else {
				$coa_posting_type->deleted_at = date('Y-m-d H:i:s');
				$coa_posting_type->deleted_by_id = Auth::user()->id;
			}
			$coa_posting_type->save();

			DB::commit();
			return response()->json(['success' => true, 'comes_from' => $msg]);
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