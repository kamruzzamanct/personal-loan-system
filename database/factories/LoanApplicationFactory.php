<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use App\Models\LoanApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoanApplication>
 */
class LoanApplicationFactory extends Factory
{
    protected $model = LoanApplication::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $employmentType = $this->faker->randomElement([
            EmploymentType::Salaried->value,
            EmploymentType::SelfEmployed->value,
        ]);

        $designation = $employmentType === EmploymentType::Salaried->value
            ? $this->faker->jobTitle()
            : null;

        $companyName = $employmentType === EmploymentType::Salaried->value
            ? $this->faker->company()
            : null;

        $livingDescription = $employmentType === EmploymentType::SelfEmployed->value
            ? $this->faker->sentence(8)
            : null;

        return [
            'user_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'age' => $this->faker->numberBetween(20, 65),
            'phone' => $this->faker->numerify('##########'),
            'address' => $this->faker->address(),
            'loan_amount' => $this->faker->randomFloat(2, 1000, 90000),
            'employment_type' => $employmentType,
            'designation' => $designation,
            'company_name' => $companyName,
            'living_description' => $livingDescription,
            'monthly_income' => $this->faker->randomFloat(2, 1000, 50000),
            'loan_proposal' => $this->faker->sentence(18),
            'consent' => true,
            'risk_level' => RiskLevel::Low->value,
            'is_self_employed' => $employmentType === EmploymentType::SelfEmployed->value,
            'status' => LoanApplicationStatus::Pending->value,
            'approved_at' => null,
            'approved_by_user_id' => null,
            'assigned_to_user_id' => null,
            'assigned_by_user_id' => null,
            'assigned_at' => null,
        ];
    }
}
