<?php

namespace RPurinton\poker;

enum PlayerType: string
{
    case HUMAN = "Human";
    case AI = "AI";

    public function display(): string
    {
        return match ($this) {
            PlayerType::HUMAN => "Human",
            PlayerType::AI => "AI",
        };
    }
}
