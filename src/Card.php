<?php

namespace RPurinton\poker;

enum Suit: string
{
    case CLUBS = 'clubs';
    case DIAMONDS = 'diamonds';
    case HEARTS = 'hearts';
    case SPADES = 'spades';

    public static function values(): array
    {
        return [
            self::CLUBS,
            self::DIAMONDS,
            self::HEARTS,
            self::SPADES,
        ];
    }
}

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

    public static function values(): array
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

class Card
{
    public function __construct(
        private Suit $suit,
        private Rank $rank
    ) {
    }

    public function getSuit(): Suit
    {
        return $this->suit;
    }

    public function getRank(): Rank
    {
        return $this->rank;
    }

    public function __toString(): string
    {
        return $this->rank . ' of ' . $this->suit;
    }
}
