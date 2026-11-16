<?php

namespace App\Enums;

enum VarietyType: string
{
    case Standard = 'standard';
    case Garden = 'garden';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $case) => $case->value, self::cases());
    }

    public function label(): string
    {
        return __('varieties.types.'.$this->value);
    }
}
