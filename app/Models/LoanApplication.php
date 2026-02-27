<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\EmploymentType;
use App\Enums\LoanApplicationStatus;
use App\Enums\RiskLevel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class LoanApplication extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'age',
        'phone',
        'address',
        'loan_amount',
        'employment_type',
        'designation',
        'company_name',
        'living_description',
        'monthly_income',
        'loan_proposal',
        'consent',
        'risk_level',
        'is_self_employed',
        'status',
        'approved_at',
        'approved_by_user_id',
        'assigned_to_user_id',
        'assigned_by_user_id',
        'assigned_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'loan_amount' => 'decimal:2',
            'employment_type' => EmploymentType::class,
            'monthly_income' => 'decimal:2',
            'consent' => 'boolean',
            'risk_level' => RiskLevel::class,
            'is_self_employed' => 'boolean',
            'status' => LoanApplicationStatus::class,
            'approved_at' => 'datetime',
            'assigned_at' => 'datetime',
        ];
    }

    public function approvedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_user_id');
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function applicantUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeHighRisk(Builder $query): Builder
    {
        return $query->whereIn('risk_level', RiskLevel::highRiskValues());
    }

    public function scopeLowRisk(Builder $query): Builder
    {
        return $query->where('risk_level', RiskLevel::Low->value);
    }

    public function scopeMediumRisk(Builder $query): Builder
    {
        return $query->where('risk_level', RiskLevel::Medium->value);
    }

    public function scopeVeryHighRisk(Builder $query): Builder
    {
        return $query->where('risk_level', RiskLevel::VeryHigh->value);
    }

    public function scopeSalaried(Builder $query): Builder
    {
        return $query->where('employment_type', EmploymentType::Salaried->value);
    }

    public function scopeSelfEmployed(Builder $query): Builder
    {
        return $query->where('employment_type', EmploymentType::SelfEmployed->value);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', LoanApplicationStatus::Approved->value);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', LoanApplicationStatus::Pending->value);
    }

    public function scopeUnderReview(Builder $query): Builder
    {
        return $query->where('status', LoanApplicationStatus::UnderReview->value);
    }

    public function scopeDeclined(Builder $query): Builder
    {
        return $query->where('status', LoanApplicationStatus::Declined->value);
    }
}
