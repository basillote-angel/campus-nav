<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		$driver = Schema::getConnection()->getDriverName();

		if ($driver === 'sqlite') {
			$this->convertStatusToVarcharSqlite();

			return;
		}

		// Lost items
		DB::statement("ALTER TABLE lost_items MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'LOST_REPORTED'");
		DB::statement("
			UPDATE lost_items
			SET status = CASE status
				WHEN 'open' THEN 'LOST_REPORTED'
				WHEN 'matched' THEN 'RESOLVED'
				WHEN 'closed' THEN 'RESOLVED'
				ELSE UPPER(status)
			END
		");

		// Found items
		DB::statement("ALTER TABLE found_items MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'FOUND_UNCLAIMED'");
		DB::statement("
			UPDATE found_items
			SET status = CASE status
				WHEN 'unclaimed' THEN 'FOUND_UNCLAIMED'
				WHEN 'matched' THEN 'CLAIM_PENDING'
				WHEN 'awaiting_collection' THEN 'CLAIM_APPROVED'
				WHEN 'returned' THEN 'COLLECTED'
				ELSE UPPER(status)
			END
		");

		// Claimed items
		DB::statement("ALTER TABLE claimed_items MODIFY COLUMN status VARCHAR(32) NOT NULL DEFAULT 'PENDING'");
		DB::statement("
			UPDATE claimed_items
			SET status = CASE status
				WHEN 'pending' THEN 'PENDING'
				WHEN 'approved' THEN 'APPROVED'
				WHEN 'rejected' THEN 'REJECTED'
				WHEN 'withdrawn' THEN 'WITHDRAWN'
				ELSE UPPER(status)
			END
		");
	}

	public function down(): void
	{
		$driver = Schema::getConnection()->getDriverName();

		if ($driver === 'sqlite') {
			$this->revertStatusForSqlite();

			return;
		}

		// Claimed items
		DB::statement("
			UPDATE claimed_items
			SET status = CASE status
				WHEN 'PENDING' THEN 'pending'
				WHEN 'APPROVED' THEN 'approved'
				WHEN 'REJECTED' THEN 'rejected'
				WHEN 'WITHDRAWN' THEN 'withdrawn'
				ELSE LOWER(status)
			END
		");
		DB::statement("ALTER TABLE claimed_items MODIFY COLUMN status ENUM('pending','approved','rejected','withdrawn') NOT NULL DEFAULT 'pending'");

		// Found items
		DB::statement("
			UPDATE found_items
			SET status = CASE status
				WHEN 'FOUND_UNCLAIMED' THEN 'unclaimed'
				WHEN 'CLAIM_PENDING' THEN 'matched'
				WHEN 'CLAIM_APPROVED' THEN 'awaiting_collection'
				WHEN 'COLLECTED' THEN 'returned'
				ELSE LOWER(status)
			END
		");
		DB::statement("ALTER TABLE found_items MODIFY COLUMN status ENUM('unclaimed','matched','awaiting_collection','returned') NOT NULL DEFAULT 'unclaimed'");

		// Lost items
		DB::statement("
			UPDATE lost_items
			SET status = CASE status
				WHEN 'LOST_REPORTED' THEN 'open'
				WHEN 'RESOLVED' THEN 'closed'
				ELSE LOWER(status)
			END
		");
		DB::statement("ALTER TABLE lost_items MODIFY COLUMN status ENUM('open','matched','closed') NOT NULL DEFAULT 'open'");
	}

	private function convertStatusToVarcharSqlite(): void
	{
		$this->swapStatusColumnForSqlite('lost_items', 'LOST_REPORTED', "
			CASE status
				WHEN 'open' THEN 'LOST_REPORTED'
				WHEN 'matched' THEN 'RESOLVED'
				WHEN 'closed' THEN 'RESOLVED'
				ELSE UPPER(status)
			END
		");

		$this->swapStatusColumnForSqlite('found_items', 'FOUND_UNCLAIMED', "
			CASE status
				WHEN 'unclaimed' THEN 'FOUND_UNCLAIMED'
				WHEN 'matched' THEN 'CLAIM_PENDING'
				WHEN 'awaiting_collection' THEN 'CLAIM_APPROVED'
				WHEN 'returned' THEN 'COLLECTED'
				ELSE UPPER(status)
			END
		");

		$this->swapStatusColumnForSqlite('claimed_items', 'PENDING', "
			CASE status
				WHEN 'pending' THEN 'PENDING'
				WHEN 'approved' THEN 'APPROVED'
				WHEN 'rejected' THEN 'REJECTED'
				WHEN 'withdrawn' THEN 'WITHDRAWN'
				ELSE UPPER(status)
			END
		");
	}

	private function revertStatusForSqlite(): void
	{
		$this->swapStatusColumnForSqlite('claimed_items', 'pending', "
			CASE status
				WHEN 'PENDING' THEN 'pending'
				WHEN 'APPROVED' THEN 'approved'
				WHEN 'REJECTED' THEN 'rejected'
				WHEN 'WITHDRAWN' THEN 'withdrawn'
				ELSE LOWER(status)
			END
		");

		$this->swapStatusColumnForSqlite('found_items', 'unclaimed', "
			CASE status
				WHEN 'FOUND_UNCLAIMED' THEN 'unclaimed'
				WHEN 'CLAIM_PENDING' THEN 'matched'
				WHEN 'CLAIM_APPROVED' THEN 'awaiting_collection'
				WHEN 'COLLECTED' THEN 'returned'
				ELSE LOWER(status)
			END
		");

		$this->swapStatusColumnForSqlite('lost_items', 'open', "
			CASE status
				WHEN 'LOST_REPORTED' THEN 'open'
				WHEN 'RESOLVED' THEN 'closed'
				ELSE LOWER(status)
			END
		");
	}

	private function swapStatusColumnForSqlite(string $table, string $default, string $caseExpression): void
	{
		$tempColumn = 'status_tmp_' . uniqid();
		$statusIndexes = $this->collectStatusIndexes($table);

		Schema::table($table, function (Blueprint $table) use ($tempColumn, $default) {
			$table->string($tempColumn, 32)->default($default)->after('status');
		});

		DB::statement("UPDATE {$table} SET {$tempColumn} = {$caseExpression}");

		$this->dropStatusIndexes($table, $statusIndexes);

		Schema::table($table, function (Blueprint $table) {
			$table->dropColumn('status');
		});

		Schema::table($table, function (Blueprint $table) use ($tempColumn) {
			$table->renameColumn($tempColumn, 'status');
		});

		$this->recreateStatusIndexes($table, $statusIndexes);
	}

	private function collectStatusIndexes(string $table): array
	{
		$indexes = DB::select("PRAGMA index_list('{$table}')");
		$results = [];

		foreach ($indexes as $index) {
			$name = $index->name ?? $index->Name ?? null;
			if (!$name) {
				continue;
			}

			$indexInfo = DB::select("PRAGMA index_info('{$name}')");
			$columns = array_map(static fn ($row) => $row->name ?? $row->Name ?? null, $indexInfo);

			if (in_array('status', $columns, true)) {
				$results[] = [
					'name' => $name,
					'columns' => $columns,
					'unique' => (bool) ($index->unique ?? $index->Unique ?? 0),
				];
			}
		}

		return $results;
	}

	private function dropStatusIndexes(string $table, array $indexes): void
	{
		foreach ($indexes as $index) {
			Schema::table($table, function (Blueprint $table) use ($index) {
				$table->dropIndex($index['name']);
			});
		}
	}

	private function recreateStatusIndexes(string $table, array $indexes): void
	{
		foreach ($indexes as $index) {
			$columnsList = implode(', ', array_map(static fn ($column) => "\"{$column}\"", $index['columns']));
			$unique = $index['unique'] ? 'UNIQUE ' : '';

			DB::statement("CREATE {$unique}INDEX {$index['name']} ON {$table} ({$columnsList})");
		}
	}
};