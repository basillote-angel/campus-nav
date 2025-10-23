<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('claimed_items', function (Blueprint $table) {
			$table->id();
			$table->foreignId('found_item_id')->constrained('found_items')->cascadeOnDelete();
			$table->foreignId('claimant_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('matched_lost_item_id')->nullable()->constrained('lost_items')->nullOnDelete();
			$table->text('message')->nullable();
			$table->enum('status', ['pending','approved','rejected','withdrawn'])->default('pending');
			$table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
			$table->timestamp('approved_at')->nullable();
			$table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
			$table->timestamp('rejected_at')->nullable();
			$table->text('rejection_reason')->nullable();
			$table->timestamps();

			$table->index(['found_item_id','status']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('claimed_items');
	}
};


