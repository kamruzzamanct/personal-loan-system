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

        return [
            'user_id' => null,
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->numerify('##########'),
            'loan_amount' => $this->faker->randomFloat(2, 1000, 90000),
            'employment_type' => $employmentType,
            'monthly_income' => $this->faker->randomFloat(2, 1000, 50000),
            'consent' => true,
            'risk_level' => RiskLevel::Low->value,
            'is_self_employed' => $employmentType === EmploymentType::SelfEmployed->value,
            'status' => LoanApplicationStatus::Pending->value,
            'approved_at' => null,
            'approved_by_user_id' => null,
        ];
    }
}
