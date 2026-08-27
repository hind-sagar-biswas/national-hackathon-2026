<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;

class EnumArrayRule implements ValidationRule
{
    protected array $enums;

    /**
     * @param  array  $enums  Array of enum cases or their underlying values.
     */
    public function __construct(array $enums)
    {
        $this->enums = array_map(fn ($enum) => match (true) {
            $enum instanceof \BackedEnum => $enum->value,
            $enum instanceof \UnitEnum => $enum->name,
            default => $enum,
        }, $enums);
    }

    /**
     * Run the validation rule.
     *
     * @param  Closure(string, ?string=): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($value, $this->enums, true)) {
            $fail("The selected {$attribute} is invalid.");
        }
    }
}
