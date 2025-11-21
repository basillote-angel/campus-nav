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

            if (!Schema::hasIndex('found_items', 'found_items_collection_deadline_index')) {
                $table->index('collection_deadline', 'found_items_collection_deadline_index');
            }

            if (!Schema::hasIndex('found_items', 'found_items_collected_at_index')) {
                $table->index('collected_at', 'found_items_collected_at_index');
            }
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

            if (Schema::hasIndex('found_items', 'found_items_collection_deadline_index')) {
                $table->dropIndex('found_items_collection_deadline_index');
            }

            if (Schema::hasIndex('found_items', 'found_items_collected_at_index')) {
                $table->dropIndex('found_items_collected_at_index');
            }

            $table->dropColumn([
                'collection_deadline',
                'collected_at',
                'collected_by',
            ]);
        });
    }
};




