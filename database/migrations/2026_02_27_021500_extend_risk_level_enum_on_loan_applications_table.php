<?php

declare(strict_types=1);

use App\Enums\RiskLevel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `loan_applications` MODIFY `risk_level` ENUM('low', 'medium', 'high', 'very_high') NOT NULL"
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('loan_applications')
            ->where('risk_level', RiskLevel::Medium->value)
            ->update([
                'risk_level' => RiskLevel::Low->value,
            ]);

        DB::table('loan_applications')
            ->where('risk_level', RiskLevel::VeryHigh->value)
            ->update([
                'risk_level' => RiskLevel::High->value,
            ]);

        DB::statement(
            "ALTER TABLE `loan_applications` MODIFY `risk_level` ENUM('low', 'high') NOT NULL"
        );
    }
};
