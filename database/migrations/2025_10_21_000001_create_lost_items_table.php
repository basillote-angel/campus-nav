<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('lost_items', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
			$table->string('title');
			$table->text('description')->nullable();
			$table->string('image_path')->nullable();
			$table->string('location')->nullable();
			$table->date('date_lost')->nullable();
			$table->enum('status', ['open','matched','closed'])->default('open');
			$table->timestamps();

			$table->index('user_id');
			$table->index('category_id');
			$table->index('status');
			$table->index('date_lost');
			$table->index('created_at');

			$table->fullText(['title','description'], 'lost_items_title_description_fulltext');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('lost_items');
	}
};


