<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('comments')) {
            return;
        }

        Schema::table('comments', function (Blueprint $table) {
            // Drop FK constraint to legacy `items` table so we can drop it safely later
            try {
                $table->dropForeign(['item_id']);
            } catch (\Throwable $e) {
                // ignore if already dropped
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('comments') || !Schema::hasTable('items')) {
            return;
        }
        Schema::table('comments', function (Blueprint $table) {
            // Attempt to restore FK to legacy items if it exists
            try {
                $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
            } catch (\Throwable $e) {
                // ignore
            }
        });
    }
};


