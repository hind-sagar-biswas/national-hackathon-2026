<?php

namespace App\Http\Requests\Loan;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::CREATE_LOANS) ?? false;
    }

    public function rules(): array
    {
        return [
            'lender' => ['required', 'string', 'max:255'],
            'principal_amount' => ['required', 'numeric', 'min:10'],
            'due_at' => ['required', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'lender.required' => 'Please enter the email address or phone number of the prospective lender.',
            'principal_amount.min' => 'Principal loan amount must be at least 10 BDT.',
            'due_at.required' => 'Please specify the proposed loan repayment due date.',
            'due_at.after' => 'Due date must be in the future.',
        ];
    }
}
