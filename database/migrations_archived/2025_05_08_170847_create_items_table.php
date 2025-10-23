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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
           $table->foreignId('owner_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('finder_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->enum('category', ['electronics', 'documents', 'accessories', 'idOrCards', 'clothing', 'bagOrPouches', 'personalItems', 'schoolSupplies','others']);   
            $table->text('description')->nullable();
            $table->enum('status', ['unclaimed', 'claimed'])->default('unclaimed');
            $table->enum('type', ['lost', 'found']);
            $table->string('location')->nullable();
            $table->date('lost_found_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
