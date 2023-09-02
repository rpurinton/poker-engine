<?php

namespace RPurinton\poker;

require_once(__DIR__ . '/Card.php');
require_once(__DIR__ . '/Player.php');
require_once(__DIR__ . '/SeatStatus.php');

class Seat
{
    private SeatStatus $status = SeatStatus::EMPTY;
    private ?Player $player = null;
    private array $cards = [];
    private Pot $pot;

    public function __construct()
    {
        $this->pot = new Pot(0);
    }
}
