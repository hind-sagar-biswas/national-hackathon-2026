<?php

namespace App\Http\Requests\User;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use App\Rules\EnumClassRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        /** @var User */
        $user = $this->user();

        return $user->hasPermissionTo(Permission::VIEW_USERS);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'role' => ['nullable', new EnumClassRule(Role::class)],
            'is_active' => 'nullable|boolean',
            'search' => 'nullable|string|max:255',
        ];
    }
}
