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
            'borrower' => ['required', 'string', 'max:255'],
            'principal_amount' => ['required', 'integer', 'min:1'],
            'due_at' => ['nullable', 'date', 'after:today'],
            'note' => ['nullable', 'string', 'max:255'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'borrower.required' => 'Please enter the email address or phone number of the borrower.',
            'principal_amount.min' => 'Principal loan amount must be at least 1.',
            'due_at.after' => 'Due date must be in the future.',
        ];
    }
}
