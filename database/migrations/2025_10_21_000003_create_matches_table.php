<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('matches', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->foreignId('lost_id')->constrained('lost_items')->cascadeOnDelete();
			$table->foreignId('found_id')->constrained('found_items')->cascadeOnDelete();
			$table->float('similarity_score');
			$table->enum('status', ['pending','confirmed','rejected'])->default('pending');
			$table->timestamps();

			$table->index('lost_id');
			$table->index('found_id');
			$table->index('status');
			$table->unique(['lost_id','found_id'], 'matches_lost_found_unique');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('matches');
	}
};


