<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		if (Schema::getConnection()->getDriverName() === 'sqlite') {
			return;
		}

		// Drop the unique index first
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->dropUnique(['token']);
		});

		// Alter the column to support longer tokens (FCM tokens can be up to 2048 chars)
		DB::statement('ALTER TABLE device_tokens MODIFY token VARCHAR(2048) NOT NULL');

		// Recreate the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->unique('token');
		});
	}

	public function down(): void
	{
		if (Schema::getConnection()->getDriverName() === 'sqlite') {
			return;
		}

		// Drop the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->dropUnique(['token']);
		});

		// Revert to original length
		DB::statement('ALTER TABLE device_tokens MODIFY token VARCHAR(255) NOT NULL');

		// Recreate the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->unique('token');
		});
	}
};

