<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
	public function up(): void
	{
		if (!Schema::hasTable('found_items')) {
			return;
		}

		Schema::table('found_items', function (Blueprint $table) {
			if (!Schema::hasColumn('found_items', 'collection_notes')) {
				$table->text('collection_notes')->nullable()->after('collected_by');
			}
		});
	}

    /**
     * Reverse the migrations.
     */
	public function down(): void
	{
		if (!Schema::hasTable('found_items')) {
			return;
		}

		Schema::table('found_items', function (Blueprint $table) {
			if (Schema::hasColumn('found_items', 'collection_notes')) {
				$table->dropColumn('collection_notes');
			}
		});
	}
};
