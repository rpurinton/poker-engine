<?php

namespace RPurinton\poker;

enum GameType: int
{
    case TEXAS_HOLDEM = 1;
    case OMAHA = 2;
    case OMAHA_HILO = 3;

    public function display()
    {
        return match ($this) {
            GameType::TEXAS_HOLDEM => 'Texas Hold \'Em',
            GameType::OMAHA => 'Omaha',
            GameType::OMAHA_HILO => 'Omaha Hi/Lo',
        };
    }

    public function num_hole_cards()
    {
        return match ($this) {
            GameType::TEXAS_HOLDEM => 2,
            GameType::OMAHA, GameType::OMAHA_HILO => 4,
        };
    }
}
