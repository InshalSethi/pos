<?php

namespace Database\Factories;

use App\Models\Department;
use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Department>
 */
class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'name' => fake()->randomElement(['Sales', 'Marketing', 'Inventory', 'Finance', 'Human Resources', 'IT Support']),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
