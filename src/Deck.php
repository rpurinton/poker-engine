<?php

namespace RPurinton\poker;

require_once 'Card.php';

class Deck
{
    private $cards = [];

    public function __construct()
    {
        $this->cards = $this->createDeck();
    }

    public function getCards()
    {
        return $this->cards;
    }

    public function shuffle()
    {
        shuffle($this->cards);
    }

    public function dealCard()
    {
        return array_pop($this->cards);
    }

    private function createDeck()
    {
        $cards = [];

        foreach (Suit::toArray() as $suit) {
            foreach (Rank::toArray() as $rank) {
                $cards[] = new Card($suit, $rank);
            }
        }

        return $cards;
    }

    public function __toString()
    {
        $deck = '';

        foreach ($this->cards as $card) {
            $deck .= $card . ' ';
        }

        return $deck;
    }
}
