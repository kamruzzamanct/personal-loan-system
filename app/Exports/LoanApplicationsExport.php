<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\LoanApplication;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class LoanApplicationsExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query)
    {
    }

    public function query(): Builder
    {
        return clone $this->query;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'ID',
            'First Name',
            'Last Name',
            'Email',
            'Phone',
            'Loan Amount',
            'Monthly Income',
            'Employment Type',
            'Risk Level',
            'Status',
            'Self Employed',
            'Submitted At',
        ];
    }

    /**
     * @param  LoanApplication  $loanApplication
     * @return list<int|float|string>
     */
    public function map($loanApplication): array
    {
        return [
            $loanApplication->id,
            $loanApplication->first_name,
            $loanApplication->last_name,
            $loanApplication->email,
            $loanApplication->phone,
            (float) $loanApplication->loan_amount,
            (float) $loanApplication->monthly_income,
            $this->enumValue($loanApplication->employment_type),
            $this->enumValue($loanApplication->risk_level),
            $this->enumValue($loanApplication->status),
            $loanApplication->is_self_employed ? 'yes' : 'no',
            (string) $loanApplication->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    private function enumValue(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            return (string) $value->value;
        }

        return (string) $value;
    }
}
