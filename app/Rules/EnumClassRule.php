<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use InvalidArgumentException;

class EnumClassRule implements ValidationRule
{
    protected array $validValues = [];

    public function __construct(string $enumClass)
    {
        if (! enum_exists($enumClass)) {
            throw new InvalidArgumentException("Class '{$enumClass}' is not a valid enum.");
        }

        if (method_exists($enumClass, 'validatable')) {
            $cases = $enumClass::validatable();
        } else {
            $cases = $enumClass::cases();
        }

        $this->validValues = array_map(fn ($case) => match (true) {
            $case instanceof \BackedEnum => $case->value,
            $case instanceof \UnitEnum => $case->name,
            default => $case,
        }, $cases);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Simple, lightning-fast lookup
        if (! in_array($value, $this->validValues, true)) {
            $fail("The selected {$attribute} is invalid.");
        }
    }
}
