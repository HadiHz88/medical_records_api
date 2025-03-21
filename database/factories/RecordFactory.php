<?php

namespace Database\Factories;

use App\Models\Record;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Record>
 */
class RecordFactory extends Factory
{
    protected $model = Record::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => Template::factory(),
        ];
    }
}
