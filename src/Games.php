<?php

namespace RPurinton\poker;

enum GameType: int
{
    case TEXAS_HOLD_EM = 1;
    case OMAHA = 2;
    case OMAHA_HILO = 3;

    public function display()
    {
        return match ($this) {
            GameType::TEXAS_HOLD_EM => 'Texas Hold \'Em',
            GameType::OMAHA => 'Omaha',
            GameType::OMAHA_HILO => 'Omaha Hi/Lo',
        };
    }
}
