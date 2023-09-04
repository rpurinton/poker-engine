<?php

namespace RPurinton\poker;

enum TableStatus: int
{
    case WAITING_FOR_PLAYERS = 0;
    case STARTING = 1;
    case PREFLOP = 2;
    case FLOP = 3;
    case TURN = 4;
    case RIVER = 5;
    case SHOWDOWN = 6;
    case HAND_OVER = 7;
    case ALLIN = 8;

    public function display(): string
    {
        return match ($this) {
            TableStatus::WAITING_FOR_PLAYERS => 'Waiting for players',
            TableStatus::STARTING => 'Starting',
            TableStatus::PREFLOP => 'Pre-flop',
            TableStatus::FLOP => 'Flop',
            TableStatus::TURN => 'Turn',
            TableStatus::RIVER => 'River',
            TableStatus::SHOWDOWN => 'Showdown',
            TableStatus::HAND_OVER => 'Hand over',
            TableStatus::ALLIN => "Everyone's All-in",
        };
    }
}
