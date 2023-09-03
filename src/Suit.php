<?php

namespace RPurinton\poker;

enum Suit
{
    case CLUBS;
    case DIAMONDS;
    case HEARTS;
    case SPADES;

    public function display(): string
    {
        return match ($this) {
            Suit::CLUBS => 'c',
            Suit::DIAMONDS => 'd',
            Suit::HEARTS => 'h',
            Suit::SPADES => 's',
        };
    }

    public function display_long(): string
    {
        return match ($this) {
            Suit::CLUBS => 'Club',
            Suit::DIAMONDS => 'Diamond',
            Suit::HEARTS => 'Heart',
            Suit::SPADES => 'Spade',
        };
    }

    public static function toArray(): array
    {
        return [
            Suit::CLUBS,
            Suit::DIAMONDS,
            Suit::HEARTS,
            Suit::SPADES,
        ];
    }
}
