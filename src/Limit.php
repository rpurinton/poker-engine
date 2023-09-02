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
            Limit::NL => 'NL',
            Limit::PL => 'PL',
            Limit::FL => 'FL',
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
