<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
	public function up(): void
	{
		Schema::create('activity_logs', function (Blueprint $table) {
			$table->engine = 'InnoDB';
			$table->id();
			$table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
			$table->string('action');
			$table->text('details')->nullable();
			$table->string('ip_address', 45)->nullable();
			$table->timestamp('created_at')->useCurrent();

			$table->index('user_id');
			$table->index('action');
			$table->index('created_at');
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('activity_logs');
	}
};


