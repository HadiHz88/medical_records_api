<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This method creates the 'templates' table with the following columns:
     * - id: Primary key, auto-incrementing
     * - name: String, required
     * - description: Text, optional
     * - timestamps: Created at and updated at timestamps
     */
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the 'templates' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
