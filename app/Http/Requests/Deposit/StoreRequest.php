<?php

namespace App\Http\Requests\Deposit;

use App\Enums\DepositProvider;
use App\Enums\Permission;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::CREATE_DEPOSITS) ?? false;
    }

    public function rules(): array
    {
        return [
            'provider' => ['required', new EnumClassRule(DepositProvider::class)],
            'provider_ref' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider_ref.required' => 'Please provide the transaction ID or receipt reference from the provider.',
            'amount.min' => 'Deposit amount must be at least 10 BDT.',
        ];
    }
}
