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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->binary('image_data')->nullable(); // Create image_data column as binary (before modifying to LONGBLOB)
            $table->boolean('processed')->default(false); // Fix the default value for processed column
            $table->timestamps();
        });

        // After creating the table, modify the column type directly with a DB statement
        DB::statement("ALTER TABLE images MODIFY image_data LONGBLOB");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};