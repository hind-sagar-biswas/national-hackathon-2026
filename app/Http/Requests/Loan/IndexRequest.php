<?php

namespace App\Http\Requests\Loan;

use App\Enums\LoanStatus;
use App\Enums\Permission;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_LOANS) ?? false;
    }

    public function rules(): array
    {
        return [
            'tab' => 'nullable|in:given,taken',
            'status' => ['nullable', new EnumClassRule(LoanStatus::class)],
            'search' => 'nullable|string|max:255',
        ];
    }
}
