<?php

declare(strict_types=1);

use App\Enums\EmploymentType;
use App\Enums\RiskLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $employmentTypes = array_map(
            static fn (EmploymentType $employmentType): string => $employmentType->value,
            EmploymentType::cases(),
        );

        $riskLevels = array_map(
            static fn (RiskLevel $riskLevel): string => $riskLevel->value,
            RiskLevel::cases(),
        );

        Schema::create('loan_applications', function (Blueprint $table) use ($employmentTypes, $riskLevels): void {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->index();
            $table->string('phone');
            $table->decimal('loan_amount', 12, 2);
            $table->enum('employment_type', $employmentTypes)->index();
            $table->decimal('monthly_income', 12, 2);
            $table->boolean('consent');
            $table->enum('risk_level', $riskLevels)->index();
            $table->boolean('is_self_employed')->index();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_applications');
    }
};
