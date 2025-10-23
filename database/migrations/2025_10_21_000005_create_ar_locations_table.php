<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('ar_locations', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->string('name');
			$table->string('building_code')->nullable();
			$table->foreignId('building_id')->nullable()->constrained('buildings')->nullOnDelete();
			$table->decimal('latitude', 10, 7)->nullable();
			$table->decimal('longitude', 10, 7)->nullable();
			$table->text('description')->nullable();
			$table->string('image_path')->nullable();
			$table->timestamps();

			$table->unique('building_code');
			$table->index('building_id');
			$table->index(['latitude','longitude']);
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('ar_locations');
	}
};


