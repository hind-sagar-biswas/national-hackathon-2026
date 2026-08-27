<?php

namespace App\Http\Requests\User;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShowRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $targetUser = $this->route('user');
        if (! $targetUser instanceof User) {
            $targetUser = User::find($targetUser);
        }

        if (! $targetUser) {
            return false;
        }

        $currentUser = $this->user();
        if ($targetUser->hasRole(Role::ADMIN)) {
            $super = User::super();

            return $currentUser->id === $super->id;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
