<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\EmploymentType;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreLoanApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'loan_amount' => ['required', 'numeric', 'min:1'],
            'employment_type' => [
                'required',
                Rule::in(array_map(
                    static fn (EmploymentType $employmentType): string => $employmentType->value,
                    EmploymentType::cases(),
                )),
            ],
            'monthly_income' => ['required', 'numeric', 'min:1'],
            'consent' => ['required', 'accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required.',
            'first_name.string' => 'First name must be a valid text value.',
            'first_name.max' => 'First name may not exceed 255 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.string' => 'Last name must be a valid text value.',
            'last_name.max' => 'Last name may not exceed 255 characters.',
            'email.required' => 'Email is required.',
            'email.email' => 'Email must be a valid email address.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a valid text value.',
            'loan_amount.required' => 'Loan amount is required.',
            'loan_amount.numeric' => 'Loan amount must be a number.',
            'loan_amount.min' => 'Loan amount must be at least 1.',
            'employment_type.required' => 'Employment type is required.',
            'employment_type.in' => 'Employment type must be salaried or self employed.',
            'monthly_income.required' => 'Monthly income is required.',
            'monthly_income.numeric' => 'Monthly income must be a number.',
            'monthly_income.min' => 'Monthly income must be at least 1.',
            'consent.required' => 'Consent is required before submitting the application.',
            'consent.accepted' => 'You must accept consent to continue.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $employmentType = $this->input('employment_type');

        if (! is_string($employmentType)) {
            return;
        }

        $this->merge([
            'employment_type' => strtolower(str_replace('-', '_', trim($employmentType))),
        ]);
    }

    protected function failedValidation(Validator $validator): void
    {
        if ($this->expectsJson()) {
            throw new HttpResponseException(response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422));
        }

        parent::failedValidation($validator);
    }
}
