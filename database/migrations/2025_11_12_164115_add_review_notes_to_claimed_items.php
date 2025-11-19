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
		if (!Schema::hasTable('claimed_items')) {
			return;
		}

		Schema::table('claimed_items', function (Blueprint $table) {
			if (!Schema::hasColumn('claimed_items', 'review_notes')) {
				$table->text('review_notes')->nullable()->after('rejection_reason');
			}
		});
	}

    /**
     * Reverse the migrations.
     */
	public function down(): void
	{
		if (!Schema::hasTable('claimed_items')) {
			return;
		}

		Schema::table('claimed_items', function (Blueprint $table) {
			if (Schema::hasColumn('claimed_items', 'review_notes')) {
				$table->dropColumn('review_notes');
			}
		});
	}
};
