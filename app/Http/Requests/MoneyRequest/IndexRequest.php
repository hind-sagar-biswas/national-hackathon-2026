<?php

namespace App\Http\Requests\MoneyRequest;

use App\Enums\Permission;
use App\Enums\RequestStatus;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_MONEY_REQUESTS) ?? false;
    }

    public function rules(): array
    {
        return [
            'tab' => 'nullable|in:incoming,outgoing',
            'status' => ['nullable', new EnumClassRule(RequestStatus::class)],
            'search' => 'nullable|string|max:255',
        ];
    }
}
