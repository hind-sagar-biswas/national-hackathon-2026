<?php

namespace App\Http\Requests\BillSplit;

use App\Enums\BillSplitMode;
use App\Enums\Permission;
use App\Models\User;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::CREATE_BILL_SPLITS) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $participants = $this->input('participants');
        if (is_array($participants)) {
            foreach ($participants as $index => $participant) {
                if (empty($participant['user_id']) && ! empty($participant['identifier'])) {
                    $identifier = trim($participant['identifier']);
                    $foundUser = User::where('email', $identifier)
                        ->orWhere('phone', $identifier)
                        ->first();
                    if ($foundUser) {
                        $participants[$index]['user_id'] = $foundUser->id;
                    }
                }
            }
            $this->merge(['participants' => $participants]);
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'total_amount' => ['required', 'numeric', 'min:10'],
            'mode' => ['required', new EnumClassRule(BillSplitMode::class)],
            'participants' => ['required', 'array', 'min:1'],
            'participants.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'participants.*.value' => ['nullable', 'numeric', 'min:0.01'],
            'merchant_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'merchant_name' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:500'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:30'],
            'idempotency_key' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the bill split.',
            'total_amount.min' => 'Total bill amount must be at least 10 BDT.',
            'participants.min' => 'Please select at least 1 other participant to split the bill with.',
            'participants.*.user_id.exists' => 'One or more participants could not be found with the provided email or phone.',
            'participants.*.user_id.required' => 'Please specify valid participants with registered email or phone.',
        ];
    }
}
