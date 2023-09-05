<?php

namespace RPurinton\poker;

enum Rank: int
{
    case TWO = 2;
    case THREE = 3;
    case FOUR = 4;
    case FIVE = 5;
    case SIX = 6;
    case SEVEN = 7;
    case EIGHT = 8;
    case NINE = 9;
    case TEN = 10;
    case JACK = 11;
    case QUEEN = 12;
    case KING = 13;
    case ACE = 14;

    public function display(): string
    {
        return match ($this) {
            self::TWO => '2',
            self::THREE => '3',
            self::FOUR => '4',
            self::FIVE => '5',
            self::SIX => '6',
            self::SEVEN => '7',
            self::EIGHT => '8',
            self::NINE => '9',
            self::TEN => 'T',
            self::JACK => 'J',
            self::QUEEN => 'Q',
            self::KING => 'K',
            self::ACE => 'A',
        };
    }

    public function display_long(): string
    {
        return match ($this) {
            self::TWO => 'Deuce',
            self::THREE => 'Three',
            self::FOUR => 'Four',
            self::FIVE => 'Five',
            self::SIX => 'Six',
            self::SEVEN => 'Seven',
            self::EIGHT => 'Eight',
            self::NINE => 'Nine',
            self::TEN => 'Ten',
            self::JACK => 'Jack',
            self::QUEEN => 'Queen',
            self::KING => 'King',
            self::ACE => 'Ace',
        };
    }

    public function numeric(): int
    {
        return match ($this) {
            self::TWO => 2,
            self::THREE => 3,
            self::FOUR => 4,
            self::FIVE => 5,
            self::SIX => 6,
            self::SEVEN => 7,
            self::EIGHT => 8,
            self::NINE => 9,
            self::TEN => 10,
            self::JACK => 11,
            self::QUEEN => 12,
            self::KING => 13,
            self::ACE => 14,
        };
    }

    public static function toArray(): iterable
    {
        return [
            self::TWO,
            self::THREE,
            self::FOUR,
            self::FIVE,
            self::SIX,
            self::SEVEN,
            self::EIGHT,
            self::NINE,
            self::TEN,
            self::JACK,
            self::QUEEN,
            self::KING,
            self::ACE,
        ];
    }
}
