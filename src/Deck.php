<?php

namespace RPurinton\poker;

require_once 'Card.php';

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

    public function __toArray(): array
    {
        return $this->cards;
    }
}
