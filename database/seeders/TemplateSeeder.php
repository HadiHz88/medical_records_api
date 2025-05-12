<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Template;
use App\Models\Field;
use App\Models\Option;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Employee Information Template
        $employeeTemplate = Template::create([
            'name' => 'Employee Information',
            'description' => 'Basic employee information form',
        ]);

        // Fields for Employee Template
        $fields = [
            [
                'field_name' => 'Full Name',
                'field_type' => 'text',
                'is_required' => true,
                'display_order' => 1,
            ],
            [
                'field_name' => 'Department',
                'field_type' => 'select',
                'is_required' => true,
                'display_order' => 2,
                'options' => [
                    ['option_name' => 'IT', 'option_value' => 'it'],
                    ['option_name' => 'HR', 'option_value' => 'hr'],
                    ['option_name' => 'Finance', 'option_value' => 'finance'],
                    ['option_name' => 'Marketing', 'option_value' => 'marketing'],
                ]
            ],
            [
                'field_name' => 'Employment Type',
                'field_type' => 'radio',
                'is_required' => true,
                'display_order' => 3,
                'options' => [
                    ['option_name' => 'Full Time', 'option_value' => 'full_time'],
                    ['option_name' => 'Part Time', 'option_value' => 'part_time'],
                    ['option_name' => 'Contractor', 'option_value' => 'contractor'],
                ]
            ],
            [
                'field_name' => 'Start Date',
                'field_type' => 'date',
                'is_required' => true,
                'display_order' => 4,
            ],
            [
                'field_name' => 'Salary',
                'field_type' => 'number',
                'is_required' => true,
                'display_order' => 5,
            ],
            [
                'field_name' => 'Active',
                'field_type' => 'boolean',
                'is_required' => true,
                'display_order' => 6,
            ]
        ];

        $this->createFields($employeeTemplate->id, $fields);

        // Customer Feedback Template
        $feedbackTemplate = Template::create([
            'name' => 'Customer Feedback',
            'description' => 'Form for collecting customer feedback on services',
        ]);

        // Fields for Feedback Template
        $fields = [
            [
                'field_name' => 'Customer Name',
                'field_type' => 'text',
                'is_required' => true,
                'display_order' => 1,
            ],
            [
                'field_name' => 'Email',
                'field_type' => 'text',
                'is_required' => true,
                'display_order' => 2,
            ],
            [
                'field_name' => 'Service Used',
                'field_type' => 'select',
                'is_required' => true,
                'display_order' => 3,
                'options' => [
                    ['option_name' => 'Product Support', 'option_value' => 'support'],
                    ['option_name' => 'Technical Consulting', 'option_value' => 'consulting'],
                    ['option_name' => 'Training', 'option_value' => 'training'],
                    ['option_name' => 'Implementation', 'option_value' => 'implementation'],
                ]
            ],
            [
                'field_name' => 'Satisfaction Level',
                'field_type' => 'radio',
                'is_required' => true,
                'display_order' => 4,
                'options' => [
                    ['option_name' => 'Very Satisfied', 'option_value' => '5'],
                    ['option_name' => 'Satisfied', 'option_value' => '4'],
                    ['option_name' => 'Neutral', 'option_value' => '3'],
                    ['option_name' => 'Dissatisfied', 'option_value' => '2'],
                    ['option_name' => 'Very Dissatisfied', 'option_value' => '1'],
                ]
            ],
            [
                'field_name' => 'Feedback Date',
                'field_type' => 'date',
                'is_required' => true,
                'display_order' => 5,
            ],
            [
                'field_name' => 'Would Recommend',
                'field_type' => 'boolean',
                'is_required' => true,
                'display_order' => 6,
            ]
        ];

        $this->createFields($feedbackTemplate->id, $fields);

        // Project Information Template
        $projectTemplate = Template::create([
            'name' => 'Project Information',
            'description' => 'Details about projects',
        ]);

        // Fields for Project Template
        $fields = [
            [
                'field_name' => 'Project Name',
                'field_type' => 'text',
                'is_required' => true,
                'display_order' => 1,
            ],
            [
                'field_name' => 'Project Type',
                'field_type' => 'select',
                'is_required' => true,
                'display_order' => 2,
                'options' => [
                    ['option_name' => 'Development', 'option_value' => 'development'],
                    ['option_name' => 'Design', 'option_value' => 'design'],
                    ['option_name' => 'Research', 'option_value' => 'research'],
                    ['option_name' => 'Maintenance', 'option_value' => 'maintenance'],
                ]
            ],
            [
                'field_name' => 'Priority',
                'field_type' => 'radio',
                'is_required' => true,
                'display_order' => 3,
                'options' => [
                    ['option_name' => 'High', 'option_value' => 'high'],
                    ['option_name' => 'Medium', 'option_value' => 'medium'],
                    ['option_name' => 'Low', 'option_value' => 'low'],
                ]
            ],
            [
                'field_name' => 'Budget',
                'field_type' => 'number',
                'is_required' => true,
                'display_order' => 4,
            ],
            [
                'field_name' => 'Start Date',
                'field_type' => 'date',
                'is_required' => true,
                'display_order' => 5,
            ],
            [
                'field_name' => 'End Date',
                'field_type' => 'date',
                'is_required' => true,
                'display_order' => 6,
            ],
            [
                'field_name' => 'Active',
                'field_type' => 'boolean',
                'is_required' => true,
                'display_order' => 7,
            ]
        ];

        $this->createFields($projectTemplate->id, $fields);
    }

    /**
     * Helper method to create fields and options for a template
     *
     * @param int $templateId
     * @param array $fields
     * @return void
     */
    private function createFields($templateId, $fields)
    {
        foreach ($fields as $fieldData) {
            $field = Field::create([
                'template_id' => $templateId,
                'field_name' => $fieldData['field_name'],
                'field_type' => $fieldData['field_type'],
                'is_required' => $fieldData['is_required'],
                'display_order' => $fieldData['display_order'],
            ]);

            if (isset($fieldData['options']) && in_array($fieldData['field_type'], ['select', 'radio', 'checkbox'])) {
                foreach ($fieldData['options'] as $index => $optionData) {
                    Option::create([
                        'field_id' => $field->id,
                        'option_name' => $optionData['option_name'],
                        'option_value' => $optionData['option_value'],
                    ]);
                }
            }
        }
    }
}
