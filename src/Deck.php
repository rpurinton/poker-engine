<?php

namespace RPurinton\poker;

require_once 'Card.php';
/* for refreence from Card.php:
enum Suit: string
{
    case CLUBS = 'clubs';
    case DIAMONDS = 'diamonds';
    case HEARTS = 'hearts';
    case SPADES = 'spades';
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
}
*/

use RPurinton\poker\Card;

class Deck
{
    private array $cards = [];

    public function __construct()
    {
        foreach (Suit::values() as $suit) {
            foreach (Rank::values() as $rank) {
                $this->cards[] = new Card($suit, $rank);
            }
        }
    }

    public function shuffle(): void
    {
        shuffle($this->cards);
    }

    public function deal(): Card
    {
        return array_pop($this->cards);
    }

    public function __toString(): string
    {
        $string = '';
        foreach ($this->cards as $card) {
            $string .= $card . "\n";
        }
        return $string;
    }
}
