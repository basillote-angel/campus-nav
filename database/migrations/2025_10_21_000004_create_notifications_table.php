<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('notifications', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
			$table->string('title');
			$table->text('message');
			$table->enum('type', ['AI_MATCH','ADMIN_ALERT','SYSTEM'])->default('SYSTEM');
			$table->boolean('is_read')->default(false);
			$table->timestamps();

			$table->index('user_id');
			$table->index('type');
			$table->index('is_read');
			$table->index('created_at');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('notifications');
	}
};


