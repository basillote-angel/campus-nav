<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('claimed_items')) {
            Schema::table('claimed_items', function (Blueprint $table) {
                if (!Schema::hasColumn('claimed_items', 'claimant_email')) {
                    $table->string('claimant_email')->nullable()->after('claimant_contact_info');
                }

                if (!Schema::hasColumn('claimed_items', 'claimant_phone')) {
                    $table->string('claimant_phone')->nullable()->after('claimant_email');
                }

                if (!Schema::hasColumn('claimed_items', 'claim_image')) {
                    $table->string('claim_image')->nullable()->after('claimant_phone');
                }
            });
        }

        if (Schema::hasTable('found_items')) {
            Schema::table('found_items', function (Blueprint $table) {
                if (!Schema::hasColumn('found_items', 'claimant_email')) {
                    $table->string('claimant_email')->nullable()->after('claimant_contact_info');
                }

                if (!Schema::hasColumn('found_items', 'claimant_phone')) {
                    $table->string('claimant_phone')->nullable()->after('claimant_email');
                }

                if (!Schema::hasColumn('found_items', 'claim_image')) {
                    $table->string('claim_image')->nullable()->after('claimant_phone');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('claimed_items')) {
            Schema::table('claimed_items', function (Blueprint $table) {
                if (Schema::hasColumn('claimed_items', 'claim_image')) {
                    $table->dropColumn('claim_image');
                }

                if (Schema::hasColumn('claimed_items', 'claimant_phone')) {
                    $table->dropColumn('claimant_phone');
                }

                if (Schema::hasColumn('claimed_items', 'claimant_email')) {
                    $table->dropColumn('claimant_email');
                }
            });
        }

        if (Schema::hasTable('found_items')) {
            Schema::table('found_items', function (Blueprint $table) {
                if (Schema::hasColumn('found_items', 'claim_image')) {
                    $table->dropColumn('claim_image');
                }

                if (Schema::hasColumn('found_items', 'claimant_phone')) {
                    $table->dropColumn('claimant_phone');
                }

                if (Schema::hasColumn('found_items', 'claimant_email')) {
                    $table->dropColumn('claimant_email');
                }
            });
        }
    }
};

