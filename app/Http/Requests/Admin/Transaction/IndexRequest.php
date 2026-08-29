<?php

namespace App\Http\Requests\Admin\Transaction;

use App\Enums\Permission;
use App\Enums\TransactionType;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_ALL_TRANSACTIONS) ?? false;
    }

    public function rules(): array
    {
        return [
            'type' => ['nullable', new EnumClassRule(TransactionType::class)],
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }
}
