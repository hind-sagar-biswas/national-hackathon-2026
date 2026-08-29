<?php

namespace App\Http\Requests\MoneyRequest;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::CREATE_MONEY_REQUESTS) ?? false;
    }

    public function rules(): array
    {
        return [
            'payer' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'integer', 'min:1'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'pre_hold' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'payer.required' => 'Please enter the email address or phone number of the person you are requesting money from.',
            'amount.min' => 'Requested amount must be at least 1.',
        ];
    }
}
