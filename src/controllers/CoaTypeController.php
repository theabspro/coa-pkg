<?php

namespace Abs\CoaPkg;
use Abs\CoaPkg\CoaType;
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
				DB::raw('IF(coa_types.deleted_at IS NULL, "ACTIVE", "INACTIVE") as status')
			)
			->where('coa_types.company_id', Auth::user()->company_id)
			->groupBy('coa_types.id')
			->orderBy('coa_types.id', 'Desc');

		return Datatables::of($coa_type_list)
			->addColumn('action', function ($coa_type_list) {

				$img_edit = asset('public/theme/img/table/cndn/edit.svg');
				$img_delete = asset('public/theme/img/table/cndn/delete.svg');

				return '<a href="#!/coa-pkg/coa-type/edit/' . $coa_type_list->id . '" class="">
                        <img class="img-responsive" src="' . $img_edit . '" alt="Edit" />
                    	</a>
						<a href="javascript:;"  data-toggle="modal" data-target="#coa-type-delete-modal" onclick="angular.element(this).scope().deleteCoaTypeConfirm(' . $coa_type_list->id . ')" title="Delete"><img src="' . $img_delete . '" alt="Delete" class="img-responsive delete"></a>';
			})
			->make(true);
	}

	public function getCoaTypeFormdata($id = NULL) {

		if ($id == NULL) {
			$coa_type = new CoaType;
			$this->data['title'] = 'Add Coa Type';
			$this->data['action'] = 'Add';
		} else {
			$this->data['title'] = 'Edit Coa Type';
			$this->data['action'] = 'Edit';
			$coa_type = CoaType::withTrashed()->where('id', $id)->first();
		}
		$this->data['coa_type'] = $coa_type;

		return response()->json($this->data);
	}

	public function saveCoaType(Request $request) {

		DB::beginTransaction();
		try {

			$validator = Validator::make($request->all(), [
				'name' => [
					'unique:coa_types,name,' . $request->id . ',id,company_id,' . Auth::user()->company_id,
					'required:true',
				],
			]);

			if ($validator->fails()) {
				return response()->json(['success' => false, 'errors' => $validator->errors()->all()]);
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