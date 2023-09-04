<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Enums/Suit.php');
require_once(__DIR__ . '/Enums/Rank.php');

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
        return $this->rank->display() . $this->suit->display();
    }
}
