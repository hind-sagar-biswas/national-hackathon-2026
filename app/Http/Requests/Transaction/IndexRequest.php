<?php

namespace App\Http\Requests\Transaction;

use App\Enums\Permission;
use App\Enums\TransactionType;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_TRANSACTIONS) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'type' => ['nullable', new EnumClassRule(TransactionType::class)],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }
}
