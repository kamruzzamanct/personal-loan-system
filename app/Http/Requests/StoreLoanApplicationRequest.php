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
            'age' => ['required', 'integer', 'min:20', 'max:100'],
            'phone' => ['required', 'string'],
            'address' => ['required', 'string', 'max:500'],
            'loan_amount' => ['required', 'numeric', 'min:1'],
            'employment_type' => [
                'required',
                Rule::in(array_map(
                    static fn (EmploymentType $employmentType): string => $employmentType->value,
                    EmploymentType::cases(),
                )),
            ],
            'designation' => ['nullable', 'required_if:employment_type,salaried', 'string', 'max:255'],
            'company_name' => ['nullable', 'required_if:employment_type,salaried', 'string', 'max:255'],
            'living_description' => ['nullable', 'required_if:employment_type,self_employed', 'string', 'max:500'],
            'monthly_income' => ['required', 'numeric', 'min:1'],
            'loan_proposal' => ['required', 'string', 'max:5000'],
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
            'age.required' => 'Age is required.',
            'age.integer' => 'Age must be a valid number.',
            'age.min' => 'You need to be at least 20 years old to get the loan.',
            'age.max' => 'Age must not be greater than 100.',
            'phone.required' => 'Phone number is required.',
            'phone.string' => 'Phone number must be a valid text value.',
            'address.required' => 'Address is required.',
            'address.string' => 'Address must be a valid text value.',
            'address.max' => 'Address may not exceed 500 characters.',
            'loan_amount.required' => 'Loan amount is required.',
            'loan_amount.numeric' => 'Loan amount must be a number.',
            'loan_amount.min' => 'Loan amount must be at least 1.',
            'employment_type.required' => 'Employment type is required.',
            'employment_type.in' => 'Employment type must be salaried or self employed.',
            'designation.required_if' => 'Designation is required for salaried applicants.',
            'designation.string' => 'Designation must be a valid text value.',
            'designation.max' => 'Designation may not exceed 255 characters.',
            'company_name.required_if' => 'Company name is required for salaried applicants.',
            'company_name.string' => 'Company name must be a valid text value.',
            'company_name.max' => 'Company name may not exceed 255 characters.',
            'living_description.required_if' => 'What you do for living is required for self employed applicants.',
            'living_description.string' => 'What you do for living must be valid text.',
            'living_description.max' => 'What you do for living may not exceed 500 characters.',
            'monthly_income.required' => 'Monthly income is required.',
            'monthly_income.numeric' => 'Monthly income must be a number.',
            'monthly_income.min' => 'Monthly income must be at least 1.',
            'loan_proposal.required' => 'Loan proposal is required.',
            'loan_proposal.string' => 'Loan proposal must be valid text.',
            'loan_proposal.max' => 'Loan proposal may not exceed 5000 characters.',
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
