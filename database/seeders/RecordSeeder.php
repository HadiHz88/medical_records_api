<?php

namespace Database\Seeders;

use App\Models\Field;
use App\Models\Option;
use App\Models\Record;
use App\Models\Template;
use App\Models\Value;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class RecordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Seed records for Employee Information template
        $this->seedEmployeeRecords();

        // Seed records for Customer Feedback template
        $this->seedFeedbackRecords();

        // Seed records for Project Information template
        $this->seedProjectRecords();
    }

    /**
     * Seed employee records
     */
    private function seedEmployeeRecords()
    {
        $template = Template::where('name', 'Employee Information')->first();

        if (!$template) {
            return;
        }

        $employeeData = [
            [
                'Full Name' => 'Jane Smith',
                'Department' => 'it',
                'Employment Type' => 'full_time',
                'Start Date' => '2022-01-15',
                'Salary' => 85000,
                'Active' => true
            ],
            [
                'Full Name' => 'Michael Johnson',
                'Department' => 'hr',
                'Employment Type' => 'full_time',
                'Start Date' => '2021-06-10',
                'Salary' => 75000,
                'Active' => true
            ],
            [
                'Full Name' => 'Sarah Williams',
                'Department' => 'finance',
                'Employment Type' => 'part_time',
                'Start Date' => '2023-03-20',
                'Salary' => 45000,
                'Active' => true
            ],
            [
                'Full Name' => 'David Brown',
                'Department' => 'marketing',
                'Employment Type' => 'contractor',
                'Start Date' => '2023-08-05',
                'Salary' => 90000,
                'Active' => false
            ]
        ];

        foreach ($employeeData as $data) {
            $this->createRecord($template->id, $data);
        }
    }

    /**
     * Seed customer feedback records
     */
    private function seedFeedbackRecords()
    {
        $template = Template::where('name', 'Customer Feedback')->first();

        if (!$template) {
            return;
        }

        $feedbackData = [
            [
                'Customer Name' => 'John Doe',
                'Email' => 'john.doe@example.com',
                'Service Used' => 'support',
                'Satisfaction Level' => '4',
                'Feedback Date' => '2023-12-10',
                'Would Recommend' => true
            ],
            [
                'Customer Name' => 'Emily Wilson',
                'Email' => 'emily.wilson@example.com',
                'Service Used' => 'consulting',
                'Satisfaction Level' => '5',
                'Feedback Date' => '2024-01-15',
                'Would Recommend' => true
            ],
            [
                'Customer Name' => 'Robert Miller',
                'Email' => 'robert.miller@example.com',
                'Service Used' => 'training',
                'Satisfaction Level' => '3',
                'Feedback Date' => '2024-02-05',
                'Would Recommend' => false
            ],
            [
                'Customer Name' => 'Lisa Taylor',
                'Email' => 'lisa.taylor@example.com',
                'Service Used' => 'implementation',
                'Satisfaction Level' => '2',
                'Feedback Date' => '2024-03-20',
                'Would Recommend' => false
            ]
        ];

        foreach ($feedbackData as $data) {
            $this->createRecord($template->id, $data);
        }
    }

    /**
     * Seed project records
     */
    private function seedProjectRecords()
    {
        $template = Template::where('name', 'Project Information')->first();

        if (!$template) {
            return;
        }

        $projectData = [
            [
                'Project Name' => 'Website Redesign',
                'Project Type' => 'design',
                'Priority' => 'high',
                'Budget' => 50000,
                'Start Date' => '2024-01-10',
                'End Date' => '2024-04-15',
                'Active' => true
            ],
            [
                'Project Name' => 'Mobile App Development',
                'Project Type' => 'development',
                'Priority' => 'high',
                'Budget' => 120000,
                'Start Date' => '2024-02-01',
                'End Date' => '2024-08-30',
                'Active' => true
            ],
            [
                'Project Name' => 'Market Research Study',
                'Project Type' => 'research',
                'Priority' => 'medium',
                'Budget' => 25000,
                'Start Date' => '2024-03-15',
                'End Date' => '2024-05-20',
                'Active' => true
            ],
            [
                'Project Name' => 'Legacy System Support',
                'Project Type' => 'maintenance',
                'Priority' => 'low',
                'Budget' => 15000,
                'Start Date' => '2023-10-01',
                'End Date' => '2024-10-01',
                'Active' => false
            ]
        ];

        foreach ($projectData as $data) {
            $this->createRecord($template->id, $data);
        }
    }

    /**
     * Create a record with values for a given template
     *
     * @param int $templateId
     * @param array $data
     * @return void
     */
    private function createRecord($templateId, $data)
    {
        // Create the record
        $record = Record::create([
            'template_id' => $templateId
        ]);

        // Get all fields for this template
        $fields = Field::where('template_id', $templateId)->get();

        // Create values for each field
        foreach ($fields as $field) {
            if (!isset($data[$field->field_name])) {
                continue;
            }

            $fieldValue = $data[$field->field_name];

            // Create the value record
            $value = Value::create([
                'record_id' => $record->id,
                'field_id' => $field->id,
                'value' => $fieldValue
            ]);

            // If this is a select, radio, or checkbox field, set the option_id
            if (in_array($field->field_type, ['select', 'radio', 'checkbox'])) {
                $option = Option::where('field_id', $field->id)
                    ->where('option_value', $fieldValue)
                    ->first();

                if ($option) {
                    $value->update(['option_id' => $option->id]);
                }
            }
        }
    }
}
