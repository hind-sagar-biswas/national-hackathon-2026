<?php

namespace App\Http\Requests\BillSplit;

use App\Enums\BillSplitMode;
use App\Enums\BillSplitStatus;
use App\Enums\Permission;
use App\Rules\EnumClassRule;
use Illuminate\Foundation\Http\FormRequest;

class IndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermissionTo(Permission::VIEW_BILL_SPLITS) ?? false;
    }

    public function rules(): array
    {
        return [
            'tab' => 'nullable|in:created,participating',
            'status' => ['nullable', new EnumClassRule(BillSplitStatus::class)],
            'mode' => ['nullable', new EnumClassRule(BillSplitMode::class)],
            'search' => 'nullable|string|max:255',
        ];
    }
}
