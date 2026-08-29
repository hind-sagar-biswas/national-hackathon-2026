<?php

namespace App\Http\Requests\Transfer;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::CREATE_TRANSFERS) ?? false;
    }

    public function rules(): array
    {
        return [
            'recipient' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:10'],
            'idempotency_key' => ['required', 'string', 'max:255'],
            'otp_code' => ['nullable', 'string', 'max:10'],
            'note' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'recipient.required' => 'Please enter the recipient email address or phone number.',
            'amount.min' => 'Transfer amount must be at least 10 BDT.',
            'idempotency_key.required' => 'An idempotency key is required for this operation.',
        ];
    }
}
