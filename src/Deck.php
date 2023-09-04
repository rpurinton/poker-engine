<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');

class Deck
{
    private $cards = [];

    public function __construct()
    {
        $this->cards = $this->create_deck();
    }

    public function get_cards()
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

    public function deal_card(array &$destination): void
    {
        $destination[] = array_pop($this->cards);
    }

    private function create_deck()
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
