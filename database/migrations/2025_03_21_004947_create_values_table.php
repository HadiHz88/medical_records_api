<?php

use App\Models\Field;
use App\Models\Record;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This method creates the 'values' table with the following columns:
     * - id: Primary key, auto-incrementing
     * - record_id: Foreign key referencing the 'records' table, with cascade on delete
     * - field_id: Foreign key referencing the 'fields' table, with cascade on delete
     * - value: Text, required
     * - timestamps: Created at and updated at timestamps
     */
    public function up(): void
    {
        Schema::create('values', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Record::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Field::class)->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method drops the 'values' table.
     */
    public function down(): void
    {
        Schema::dropIfExists('values');
    }
};
