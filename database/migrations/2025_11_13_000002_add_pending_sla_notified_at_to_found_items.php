<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('found_items', function (Blueprint $table) {
            if (!Schema::hasColumn('found_items', 'pending_sla_notified_at')) {
                $table->timestamp('pending_sla_notified_at')->nullable()->after('last_collection_reminder_at');
            }
        });

        DB::table('found_items')
            ->where('status', 'returned')
            ->whereNull('collected_at')
            ->update(['status' => 'awaiting_collection']);
    }

    public function down(): void
    {
        DB::table('found_items')
            ->where('status', 'awaiting_collection')
            ->update(['status' => 'returned']);

        Schema::table('found_items', function (Blueprint $table) {
            if (Schema::hasColumn('found_items', 'pending_sla_notified_at')) {
                $table->dropColumn('pending_sla_notified_at');
            }
        });
    }
};

