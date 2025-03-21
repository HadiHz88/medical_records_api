<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\Record;
use App\Models\Value;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<Value>
 */
class ValueFactory extends Factory
{
    protected $model = Value::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'record_id' => Record::factory(),
            'field_id' => Field::factory(),
            'value' => $this->faker->sentence(),
        ];
    }
}
