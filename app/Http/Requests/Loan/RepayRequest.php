<?php

namespace App\Http\Requests\Loan;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class RepayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::REPAY_LOANS) ?? false;
    }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:10'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.min' => 'Repayment amount must be at least 10 BDT.',
        ];
    }
}
