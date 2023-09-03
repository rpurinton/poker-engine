<?php

namespace RPurinton\poker;

enum Limit
{
    case NL;
    case PL;
    case FL;

    public function display(): string
    {
        return match ($this) {
            Limit::NL => 'No Limit',
            Limit::PL => 'Pot Limit',
            Limit::FL => 'Fixed Limit',
        };
    }

    public static function toArray(): array
    {
        return [
            Limit::NL,
            Limit::PL,
            Limit::FL,
        ];
    }
}
