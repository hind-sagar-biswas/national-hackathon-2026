<?php

namespace App\Http\Requests\Admin\Hold;

use App\Enums\HoldStatus;
use App\Enums\Permission;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_HOLDS) ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['nullable', new EnumClassRule(HoldStatus::class)],
            'search' => 'nullable|string|max:255',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }
}
