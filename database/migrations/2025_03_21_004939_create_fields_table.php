<?php

use App\Models\Template;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This method creates the 'fields' table with the following columns:
     * - id: Primary key, auto-incrementing
     * - template_id: Foreign key referencing the 'templates' table, with cascade on delete
     * - field_name: String, required
     * - field_type: String, required
     * - is_required: Boolean, defaults to false
     * - display_order: Integer, defaults to 1
     * - timestamps: Created at and updated at timestamps
     */
    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Template::class)->constrained()->cascadeOnDelete();
            $table->string('field_name');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->integer('display_order')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the 'fields' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};
