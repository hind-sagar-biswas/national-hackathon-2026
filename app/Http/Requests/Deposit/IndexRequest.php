<?php

namespace App\Http\Requests\Deposit;

use App\Enums\DepositProvider;
use App\Enums\DepositStatus;
use App\Enums\Permission;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_DEPOSITS) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', new EnumClassRule(DepositStatus::class)],
            'provider' => ['nullable', new EnumClassRule(DepositProvider::class)],
            'search' => 'nullable|string|max:255',
        ];
    }
}
