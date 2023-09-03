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

    public function cut()
    {
        $cut = rand(1, 51);
        $this->cards = array_merge(
            array_slice($this->cards, $cut),
            array_slice($this->cards, 0, $cut)
        );
    }

    public function dealCard(array &$destination): void
    {
        $destination[] = array_pop($this->cards);
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

    public function toString()
    {
        $deck = '';

        foreach ($this->cards as $card) {
            $deck .= $card . ' ';
        }

        return $deck;
    }
}
