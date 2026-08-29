<?php

namespace App\Http\Requests\Transfer;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_TRANSFERS) ?? false;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:50',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ];
    }
}
