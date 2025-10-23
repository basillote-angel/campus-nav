<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('item_id_mappings', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->unsignedBigInteger('old_item_id')->unique();
			$table->enum('new_type', ['lost','found']);
			$table->unsignedBigInteger('new_id');
			$table->timestamps();

			$table->index(['new_type','new_id']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('item_id_mappings');
	}
};


