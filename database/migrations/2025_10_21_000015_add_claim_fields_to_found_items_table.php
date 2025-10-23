<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::table('found_items', function (Blueprint $table) {
			$table->unsignedBigInteger('claimed_by')->nullable()->after('status');
			$table->timestamp('claimed_at')->nullable()->after('claimed_by');
			$table->text('claim_message')->nullable()->after('claimed_at');
			$table->timestamp('approved_at')->nullable()->after('claim_message');
			$table->unsignedBigInteger('approved_by')->nullable()->after('approved_at');
			$table->timestamp('rejected_at')->nullable()->after('approved_by');
			$table->unsignedBigInteger('rejected_by')->nullable()->after('rejected_at');
			$table->text('rejection_reason')->nullable()->after('rejected_by');

			$table->foreign('claimed_by')->references('id')->on('users')->onDelete('set null');
			$table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
			$table->foreign('rejected_by')->references('id')->on('users')->onDelete('set null');
		});
	}

	public function down(): void
	{
		Schema::table('found_items', function (Blueprint $table) {
			$table->dropForeign(['claimed_by']);
			$table->dropForeign(['approved_by']);
			$table->dropForeign(['rejected_by']);
			$table->dropColumn([
				'claimed_by',
				'claimed_at',
				'claim_message',
				'approved_at',
				'approved_by',
				'rejected_at',
				'rejected_by',
				'rejection_reason',
			]);
		});
	}
};


