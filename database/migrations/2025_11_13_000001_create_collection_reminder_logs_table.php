<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collection_reminder_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('found_item_id')->constrained('found_items')->cascadeOnDelete();
            $table->foreignId('claimant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('stage')->nullable();
            $table->string('source')->default('auto'); // auto | manual
            $table->string('status')->default('pending'); // pending | converted | expired
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('converted_at')->nullable();
            $table->integer('minutes_to_collection')->nullable();
            $table->timestamps();

            $table->index(['found_item_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collection_reminder_logs');
    }
};




















