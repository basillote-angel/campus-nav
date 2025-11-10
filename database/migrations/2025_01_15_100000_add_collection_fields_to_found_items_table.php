<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		if (!Schema::hasTable('found_items')) {
			return;
		}

		Schema::table('found_items', function (Blueprint $table) {
			if (!Schema::hasColumn('found_items', 'collection_deadline')) {
				$table->dateTime('collection_deadline')->nullable()->after('approved_at');
			}

			if (!Schema::hasColumn('found_items', 'collected_at')) {
				$table->timestamp('collected_at')->nullable()->after('collection_deadline');
			}

			if (!Schema::hasColumn('found_items', 'collected_by')) {
				$table->unsignedBigInteger('collected_by')->nullable()->after('collected_at');
				$table->foreign('collected_by')->references('id')->on('users')->onDelete('set null');
			}

			$table->index('collection_deadline');
			$table->index('collected_at');
		});
	}

	public function down(): void
	{
		if (!Schema::hasTable('found_items')) {
			return;
		}

		Schema::table('found_items', function (Blueprint $table) {
			if (Schema::hasColumn('found_items', 'collected_by')) {
				$table->dropForeign(['collected_by']);
			}

			if (Schema::hasColumn('found_items', 'collection_deadline')) {
				$table->dropIndex(['collection_deadline']);
			}

			if (Schema::hasColumn('found_items', 'collected_at')) {
				$table->dropIndex(['collected_at']);
			}

			$table->dropColumn([
				'collection_deadline',
				'collected_at',
				'collected_by',
			]);
		});
	}
};

