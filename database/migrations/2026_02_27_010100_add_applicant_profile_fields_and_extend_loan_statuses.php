<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table): void {
            $table->unsignedTinyInteger('age')->nullable()->after('email');
            $table->string('designation')->nullable()->after('employment_type');
            $table->string('company_name')->nullable()->after('designation');
            $table->string('living_description')->nullable()->after('company_name');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement(
                "ALTER TABLE `loan_applications` MODIFY `status` ENUM('pending', 'under_review', 'approved', 'declined') NOT NULL DEFAULT 'pending'"
            );
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('loan_applications')
                ->whereIn('status', ['under_review', 'declined'])
                ->update(['status' => 'pending']);

            DB::statement(
                "ALTER TABLE `loan_applications` MODIFY `status` ENUM('pending', 'approved') NOT NULL DEFAULT 'pending'"
            );
        }

        Schema::table('loan_applications', function (Blueprint $table): void {
            $table->dropColumn([
                'age',
                'designation',
                'company_name',
                'living_description',
            ]);
        });
    }
};
