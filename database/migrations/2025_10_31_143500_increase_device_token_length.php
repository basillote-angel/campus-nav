<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
	public function up(): void
	{
		$driver = Schema::getConnection()->getDriverName();
		
		if ($driver === 'sqlite') {
			return;
		}

		// Drop the unique index first
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->dropUnique(['token']);
		});

		// Alter the column to support longer tokens (FCM tokens can be up to 2048 chars)
		if ($driver === 'pgsql') {
			// PostgreSQL syntax
			DB::statement('ALTER TABLE device_tokens ALTER COLUMN token TYPE VARCHAR(2048)');
			DB::statement('ALTER TABLE device_tokens ALTER COLUMN token SET NOT NULL');
		} else {
			// MySQL syntax
			DB::statement('ALTER TABLE device_tokens MODIFY token VARCHAR(2048) NOT NULL');
		}

		// Recreate the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->unique('token');
		});
	}

	public function down(): void
	{
		$driver = Schema::getConnection()->getDriverName();
		
		if ($driver === 'sqlite') {
			return;
		}

		// Drop the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->dropUnique(['token']);
		});

		// Revert to original length
		if ($driver === 'pgsql') {
			// PostgreSQL syntax
			DB::statement('ALTER TABLE device_tokens ALTER COLUMN token TYPE VARCHAR(255)');
			DB::statement('ALTER TABLE device_tokens ALTER COLUMN token SET NOT NULL');
		} else {
			// MySQL syntax
			DB::statement('ALTER TABLE device_tokens MODIFY token VARCHAR(255) NOT NULL');
		}

		// Recreate the unique index
		Schema::table('device_tokens', function (Blueprint $table) {
			$table->unique('token');
		});
	}
};

