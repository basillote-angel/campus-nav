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
		if (Schema::hasTable('found_items')) {
			Schema::table('found_items', function (Blueprint $table) {
				if (!Schema::hasColumn('found_items', 'claimant_contact_name')) {
					$table->string('claimant_contact_name')->nullable()->after('claim_message');
				}

				if (!Schema::hasColumn('found_items', 'claimant_contact_info')) {
					$table->string('claimant_contact_info')->nullable()->after('claimant_contact_name');
				}
			});
		}

		if (Schema::hasTable('claimed_items')) {
			Schema::table('claimed_items', function (Blueprint $table) {
				if (!Schema::hasColumn('claimed_items', 'claimant_contact_name')) {
					$table->string('claimant_contact_name')->nullable()->after('message');
				}

				if (!Schema::hasColumn('claimed_items', 'claimant_contact_info')) {
					$table->string('claimant_contact_info')->nullable()->after('claimant_contact_name');
				}
			});
		}
	}

    /**
     * Reverse the migrations.
     */
	public function down(): void
	{
		if (Schema::hasTable('found_items')) {
			Schema::table('found_items', function (Blueprint $table) {
				if (Schema::hasColumn('found_items', 'claimant_contact_info')) {
					$table->dropColumn('claimant_contact_info');
				}

				if (Schema::hasColumn('found_items', 'claimant_contact_name')) {
					$table->dropColumn('claimant_contact_name');
				}
			});
		}

		if (Schema::hasTable('claimed_items')) {
			Schema::table('claimed_items', function (Blueprint $table) {
				if (Schema::hasColumn('claimed_items', 'claimant_contact_info')) {
					$table->dropColumn('claimant_contact_info');
				}

				if (Schema::hasColumn('claimed_items', 'claimant_contact_name')) {
					$table->dropColumn('claimant_contact_name');
				}
			});
		}
	}
};
