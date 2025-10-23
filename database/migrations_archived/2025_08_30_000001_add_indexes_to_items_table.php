<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            // Filter/sort indexes
            $table->index('type', 'items_type_index');
            $table->index('category', 'items_category_index');
            $table->index('status', 'items_status_index');
            $table->index('lost_found_date', 'items_lost_found_date_index');
            $table->index('created_at', 'items_created_at_index');

            // Keyword search (MySQL/InnoDB)
            $table->fullText(['name', 'description'], 'items_name_description_fulltext');
        });
    }

    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex('items_type_index');
            $table->dropIndex('items_category_index');
            $table->dropIndex('items_status_index');
            $table->dropIndex('items_lost_found_date_index');
            $table->dropIndex('items_created_at_index');
            $table->dropIndex('items_name_description_fulltext');
        });
    }
};


