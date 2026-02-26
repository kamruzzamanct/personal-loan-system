<?php

declare(strict_types=1);

use App\Enums\AdminRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->enum('role', array_map(
                static fn (AdminRole $role): string => $role->value,
                AdminRole::cases(),
            ))
                ->default(AdminRole::Customer->value)
                ->after('password')
                ->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};
