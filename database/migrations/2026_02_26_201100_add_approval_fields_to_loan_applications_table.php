<?php

declare(strict_types=1);

use App\Enums\LoanApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table): void {
            $table->enum('status', array_map(
                static fn (LoanApplicationStatus $status): string => $status->value,
                LoanApplicationStatus::cases(),
            ))
                ->default(LoanApplicationStatus::Pending->value)
                ->after('is_self_employed')
                ->index();

            $table->timestamp('approved_at')->nullable()->after('status');
            $table->foreignId('approved_by_user_id')
                ->nullable()
                ->after('approved_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('approved_by_user_id');
            $table->dropColumn(['approved_at', 'status']);
        });
    }
};
