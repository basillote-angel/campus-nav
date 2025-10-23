<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		foreach (['failed_jobs', 'job_batches', 'jobs', 'cache', 'cache_locks'] as $table) {
			if (Schema::hasTable($table)) {
				Schema::drop($table);
			}
		}
	}

	public function down(): void
	{
		// No-op: these scaffolding tables won't be recreated here
	}
};


