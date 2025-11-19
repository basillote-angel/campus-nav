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
			if (!Schema::hasColumn('found_items', 'last_collection_reminder_at')) {
				$table->dateTime('last_collection_reminder_at')->nullable()->after('collection_deadline');
			}

			if (!Schema::hasColumn('found_items', 'collection_reminder_stage')) {
				$table->string('collection_reminder_stage', 32)->nullable()->after('last_collection_reminder_at');
			}

			if (!Schema::hasColumn('found_items', 'overdue_notified_at')) {
				$table->dateTime('overdue_notified_at')->nullable()->after('collection_reminder_stage');
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
			if (Schema::hasColumn('found_items', 'overdue_notified_at')) {
				$table->dropColumn('overdue_notified_at');
			}

			if (Schema::hasColumn('found_items', 'collection_reminder_stage')) {
				$table->dropColumn('collection_reminder_stage');
			}

			if (Schema::hasColumn('found_items', 'last_collection_reminder_at')) {
				$table->dropColumn('last_collection_reminder_at');
			}
		});
	}
};
