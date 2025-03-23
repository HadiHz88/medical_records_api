<?php

use App\Models\Template;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This method creates the 'records' table with the following columns:
     * - id: Primary key, auto-incrementing
     * - template_id: Foreign key referencing the 'templates' table, with cascade on delete
     * - timestamps: Created at and updated at timestamps
     */
    public function up(): void
    {
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Template::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the 'records' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};
