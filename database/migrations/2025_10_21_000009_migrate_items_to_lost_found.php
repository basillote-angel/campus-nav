<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		// Best-effort data migration from legacy `items` to new tables.
		if (!DB::getSchemaBuilder()->hasTable('items')) {
			return;
		}

		$items = DB::table('items')->orderBy('id')->get();

		foreach ($items as $item) {
			$categoryId = null;
			if (DB::getSchemaBuilder()->hasTable('categories')) {
				$existing = DB::table('categories')->where('name', $item->category)->first();
				if ($existing) {
					$categoryId = $existing->id;
				} else {
					// Create category on-the-fly to satisfy NOT NULL FK
					$categoryId = DB::table('categories')->insertGetId([
						'name' => $item->category ?? 'uncategorized',
						'description' => null,
						'created_at' => now(),
						'updated_at' => now(),
					]);
				}
			}

			if ($item->type === 'lost') {
				$newId = DB::table('lost_items')->insertGetId([
					'user_id' => $item->owner_id,
					'category_id' => $categoryId,
					'title' => $item->name,
					'description' => $item->description,
					'image_path' => null,
					'location' => $item->location,
					'date_lost' => $item->lost_found_date,
					'status' => $item->status === 'claimed' ? 'closed' : 'open',
					'created_at' => $item->created_at,
					'updated_at' => $item->updated_at,
				]);

				DB::table('item_id_mappings')->insert([
					'old_item_id' => $item->id,
					'new_type' => 'lost',
					'new_id' => $newId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			} elseif ($item->type === 'found') {
				$newId = DB::table('found_items')->insertGetId([
					'user_id' => $item->finder_id,
					'category_id' => $categoryId,
					'title' => $item->name,
					'description' => $item->description,
					'image_path' => null,
					'location' => $item->location,
					'date_found' => $item->lost_found_date,
					'status' => $item->status === 'claimed' ? 'returned' : 'unclaimed',
					'created_at' => $item->created_at,
					'updated_at' => $item->updated_at,
				]);

				DB::table('item_id_mappings')->insert([
					'old_item_id' => $item->id,
					'new_type' => 'found',
					'new_id' => $newId,
					'created_at' => now(),
					'updated_at' => now(),
				]);
			}
		}
	}

	public function down(): void
	{
		// Do nothing. We won't try to map back to legacy `items`.
	}
};


