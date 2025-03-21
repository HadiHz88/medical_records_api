<?php

namespace Database\Factories;

use App\Models\Field;
use App\Models\Template;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Field>
 */
class FieldFactory extends Factory
{
    protected $model = Field::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'template_id' => Template::factory(),
            'field_name' => $this->faker->word(),
            'field_type' => $this->faker->randomElement(['text', 'number', 'date', 'checkbox', 'textarea']),
            'is_required' => $this->faker->boolean(),
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
