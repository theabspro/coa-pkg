<?php
namespace Abs\CoaPkg\Database\Seeds;

use App\Permission;
use Illuminate\Database\Seeder;

class COAPermissionSeeder extends Seeder {
	/**
	 * Run the database seeds.
	 *
	 * @return void
	 */
	public function run() {
		$permissions = [
			//MASTER > COA TYPES & CODES
			3500 => [
				'display_order' => 11,
				'parent_id' => 2,
				'name' => 'coa-types',
				'display_name' => 'COA Types',
			],
			3501 => [
				'display_order' => 1,
				'parent_id' => 3500,
				'name' => 'add-coa-type',
				'display_name' => 'Add',
			],
			3502 => [
				'display_order' => 2,
				'parent_id' => 3500,
				'name' => 'edit-coa-type',
				'display_name' => 'Edit',
			],
			3503 => [
				'display_order' => 3,
				'parent_id' => 3500,
				'name' => 'delete-coa-type',
				'display_name' => 'Delete',
			],

			//MASTER > COA POSTING TYPES
			3520 => [
				'display_order' => 11,
				'parent_id' => 2,
				'name' => 'coa-posting-types',
				'display_name' => 'COA Posting Types',
			],
			3521 => [
				'display_order' => 1,
				'parent_id' => 3520,
				'name' => 'add-coa-posting-type',
				'display_name' => 'Add',
			],
			3522 => [
				'display_order' => 2,
				'parent_id' => 3520,
				'name' => 'edit-coa-posting-type',
				'display_name' => 'Edit',
			],
			3523 => [
				'display_order' => 3,
				'parent_id' => 3520,
				'name' => 'delete-coa-posting-type',
				'display_name' => 'Delete',
			],

		];

		foreach ($permissions as $permission_id => $permsion) {
			$permission = Permission::firstOrNew([
				'id' => $permission_id,
			]);
			$permission->fill($permsion);
			$permission->save();
		}
		//$this->call(RoleSeeder::class);

	}
}