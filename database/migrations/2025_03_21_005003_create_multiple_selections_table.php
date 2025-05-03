<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Value;
use App\Models\Option;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('multiple_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Value::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Option::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_selections');
    }
};