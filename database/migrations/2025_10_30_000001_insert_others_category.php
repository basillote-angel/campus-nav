<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('categories')) {
			return;
		}

		$exists = DB::table('categories')->where('name', 'Others')->exists();
		if (!$exists) {
			DB::table('categories')->insert([
				'name' => 'Others',
				'description' => null,
				'created_at' => now(),
				'updated_at' => now(),
			]);
		}
	}

	public function down(): void
	{
		if (!Schema::hasTable('categories')) {
			return;
		}

		DB::table('categories')->where('name', 'Others')->delete();
	}
};








