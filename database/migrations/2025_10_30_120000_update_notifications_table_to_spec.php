<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	private function hasIndex(string $tableName, string $indexName): bool
	{
		$driver = Schema::getConnection()->getDriverName();

		if ($driver === 'sqlite') {
			$result = DB::select("PRAGMA index_list('{$tableName}')");
			foreach ($result as $row) {
				$name = $row->name ?? $row->Name ?? null;
				if ($name === $indexName) {
					return true;
				}
			}
			return false;
		}

		$dbName = DB::getDatabaseName();
		$result = DB::select(
			"SELECT COUNT(*) as count FROM information_schema.statistics 
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
			[$dbName, $tableName, $indexName]
		);
		return $result[0]->count > 0;
	}

    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        Schema::table('notifications', function (Blueprint $table) use ($driver) {
            // Convert enum type to string
            $table->string('type')->default('system_alert')->change();

            // Rename message -> body
            if (Schema::hasColumn('notifications', 'message')) {
                $table->renameColumn('message', 'body');
            }

            // New columns
            if (!Schema::hasColumn('notifications', 'related_id')) {
                $table->unsignedBigInteger('related_id')->nullable()->after('body');
            }
            if (!Schema::hasColumn('notifications', 'score')) {
                $table->decimal('score', 4, 2)->nullable()->after('related_id');
            }
            if (!Schema::hasColumn('notifications', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('score');
            }

            // Drop old boolean is_read if exists
            if (Schema::hasColumn('notifications', 'is_read')) {
                if ($driver !== 'sqlite') {
                    $table->dropColumn('is_read');
                }
            }
        });

        // Indexes (check before creating to avoid duplicates)
        $compositeIndexName = 'notifications_user_id_read_at_index';
        if (!$this->hasIndex('notifications', $compositeIndexName)) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index(['user_id', 'read_at']);
            });
        }

        // type and created_at may already exist from original migration
        if (!$this->hasIndex('notifications', 'notifications_type_index')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('type');
            });
        }

        if (!$this->hasIndex('notifications', 'notifications_created_at_index')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->index('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // revert type to original enum if needed
            $table->enum('type', ['AI_MATCH','ADMIN_ALERT','SYSTEM'])->default('SYSTEM')->change();

            if (Schema::hasColumn('notifications', 'body')) {
                $table->renameColumn('body', 'message');
            }

            if (Schema::hasColumn('notifications', 'related_id')) {
                $table->dropColumn('related_id');
            }
            if (Schema::hasColumn('notifications', 'score')) {
                $table->dropColumn('score');
            }
            if (Schema::hasColumn('notifications', 'read_at')) {
                $table->dropColumn('read_at');
            }

            if (!Schema::hasColumn('notifications', 'is_read')) {
                $table->boolean('is_read')->default(false);
            }
        });
    }
};


